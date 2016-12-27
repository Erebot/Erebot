# -*- coding: utf-8 -*-

import os
from os.path import join
import sys
import glob
import shutil
import urllib
import fnmatch
from datetime import datetime
from subprocess import call, Popen, PIPE

try:
    import simplejson as json
except ImportError:
    import json

def prepare(globs, locs):
    # Where are we?
    cwd = os.getcwd()
    root = os.path.abspath(join(cwd, '..', '..'))

    git = Popen('which git 2> %s' % os.devnull, shell=True,
                stdout=PIPE).stdout.read().strip()
    doxygen = Popen('which doxygen 2> %s' % os.devnull, shell=True,
                stdout=PIPE).stdout.read().strip()

    locs['rtd_slug'] = os.path.basename(os.path.dirname(os.path.dirname(root)))
    locs['rtd_version'] = os.path.basename(root)
    pybabel = join(root, '..', '..', 'envs', locs['rtd_version'], 'bin', 'pybabel')

    print "git version:"
    call([git, '--version'])
    print "doxygen version:"
    call([doxygen, '--version'])
    print "pybabel version:"
    call([pybabel, '--version'])

    print "Building version %s for %s in %s..." % (
        locs['rtd_version'],
        locs['rtd_slug'],
        root
    )
    os.chdir(root)

    # Figure several configuration values from git.
    origin = Popen([git, 'config', '--local', 'remote.origin.url'],
                    stdout=PIPE).stdout.read().strip()
    git_tag = Popen([git, 'describe', '--tags', '--exact', '--first-parent'],
                    stdout=PIPE).communicate()[0].strip()
    git_hash = Popen([git, 'rev-parse', 'HEAD'],
                    stdout=PIPE).communicate()[0].strip()
    project = origin.rpartition('/')[2]
    if project.endswith('.git'):
        project = project[:-4]
    os.environ['SPHINX_PROJECT'] = project
    if git_tag:
        os.environ['SPHINX_VERSION'] = git_tag
        os.environ['SPHINX_RELEASE'] = git_tag
    else:
        commit = Popen([git, 'describe', '--always', '--first-parent'],
                        stdout=PIPE).communicate()[0].strip()
        os.environ['SPHINX_VERSION'] = 'latest'
        os.environ['SPHINX_RELEASE'] = 'latest-%s' % (commit, )
        locs['tags'].add('devel')

    # Clone or update dependencies
    for repository, path in (
        ('git://github.com/Erebot/Buildenv.git', join(root, 'vendor', 'erebot', 'buildenv')),
        ('git://github.com/fpoirotte/PHPNatives4Doxygen', join(root, 'vendor', 'fpoirotte', 'natives4doxygen')),
        ('git://github.com/Erebot/GenericDoc.git', join(root, 'docs', 'src', 'generic')),
        ('git://github.com/Erebot/API.git', join(root, 'vendor', 'erebot', 'api')),
        ('git://github.com/Erebot/Intl.git', join(root, 'vendor', 'erebot', 'intl')),
        ('git://github.com/Erebot/CallableWrapper.git', join(root, 'vendor', 'erebot', 'callable-wrapper')),
        ('git://github.com/Erebot/Timer.git', join(root, 'vendor', 'erebot', 'timer')),
        ('git://github.com/Erebot/Styling.git', join(root, 'vendor', 'erebot', 'styling')),
        ('git://github.com/Erebot/DOM.git', join(root, 'vendor', 'erebot', 'dom')),
        ('git://github.com/Erebot/URI.git', join(root, 'vendor', 'erebot', 'uri')),
    ):
        if not os.path.isdir(path):
            os.makedirs(path)
            print "Cloning %s into %s..." % (repository, path)
            call([git, 'clone', repository, path])
        elif os.path.isdir(join(path, '.git')):
            os.chdir(path)
            print "Updating clone of %s in %s..." % (repository, path)
            call([git, 'checkout', 'master'])
            call([git, 'pull'])
            os.chdir(root)

    composer = json.load(open(join(root, 'composer.json'), 'r'))

    # Run doxygen
    call([doxygen, join(root, 'Doxyfile')], env={
        'COMPONENT_NAME': os.environ['SPHINX_PROJECT'],
        'COMPONENT_VERSION': os.environ['SPHINX_VERSION'],
        'COMPONENT_BRIEF': composer.get('description', ''),
    })

    # Remove extra files/folders.
    try:
        shutil.rmtree(join(root, 'build'))
    except OSError:
        pass
    os.mkdir(join(root, 'build'))
    shutil.move(
        join(root, 'docs', 'api', 'html'),
        join(root, 'build', 'apidoc'),
    )
    try:
        shutil.move(
            join(root, '%s.tagfile.xml' %
                os.environ['SPHINX_PROJECT']),
            join(root, 'build', 'apidoc', '%s.tagfile.xml' %
                os.environ['SPHINX_PROJECT'])
        )
    except OSError:
        pass

    # Copy translations for generic docs to catalogs folder.
    gen_i18n = join(root, 'docs', 'src', 'generic', 'i18n', '.')[:-1]
    for translation in glob.iglob(join(gen_i18n, '*')):
        target_dir = join(
            root, 'docs', 'i18n',
            translation[len(gen_i18n):],
            'LC_MESSAGES', 'generic'
        )
        translation = join(translation, 'LC_MESSAGES', 'generic')
        shutil.rmtree(target_dir, ignore_errors=True)
        shutil.copytree(translation, target_dir)

    # Compile translation catalogs.
    for locale_dir in glob.iglob(join(root, 'docs', 'i18n', '*')):
        for base, dirnames, filenames in os.walk(locale_dir):
            for po in fnmatch.filter(filenames, '*.po'):
                po = join(base, po)
                mo = po[:-3] + '.mo'
                call([pybabel, 'compile', '-f', '--statistics',
                      '-i', po, '-o', mo])

    # Load the real Sphinx configuration file.
    os.chdir(cwd)
    real_conf = join(buildenv, 'sphinx', 'conf.py')
    print "Including real configuration file (%s)..." % (real_conf, )
    execfile(real_conf, globs, locs)

    # Patch configuration afterwards.
    # - Theme
    if 'html_extra_path' not in locs:
        locs['html_extra_path'] = []
    locs['html_extra_path'].append(join(root, 'build'))
    locs['html_theme'] = 'haiku'

    # - I18N
    if 'locale_dirs' not in locs:
        locs['locale_dirs'] = []
    locs['locale_dirs'].insert(0, join(root, 'docs', 'i18n'))

    if 'rst_prolog' not in locs:
        locs['rst_prolog'] = ''
    locs['rst_prolog'] += '\n    .. _`this_commit`: https://github.com/%s/commit/%s\n' % (
        project,
        git_hash,
    )

prepare(globals(), locals())
