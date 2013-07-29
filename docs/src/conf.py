# -*- coding: utf-8 -*-

import os
from subprocess import call, Popen, PIPE

def prepare(globs, locs):
    git = Popen('which git 2> %s' % os.devnull, shell=True, stdout=PIPE
                ).stdout.read().strip()
    cwd = os.getcwd()
    root = os.path.abspath(os.path.join(cwd, '..', '..'))
    print "Running from %s..." % (root, )

    buildenv = os.path.join(root, 'vendor', 'erebot', 'buildenv')
    generic_doc = os.path.join(root, 'docs', 'src', 'generic')

    origin = Popen([git, 'config', '--local', 'remote.origin.url'],
                   stdout=PIPE).stdout.read().strip()
    project = origin.rpartition('/')[2]
    if project.endswith('.git'):
        project = project[:-4]
    locs['project'] = project

    for repository, path in (
        ('git://github.com/Erebot/Erebot_Buildenv.git', buildenv),
        ('git://github.com/Erebot/Erebot_Module_Skeleton_Doc.git', generic_doc)
    ):
        if not os.path.isdir(path):
            os.makedirs(path)
            print "Cloning %s into %s..." % (repository, path)
            call([git, 'clone', repository, path])
        else:
            os.chdir(path)
            print "Updating clone of %s in %s..." % (repository, path)
            call([git, 'checkout', 'master'])
            call([git, 'pull'])
            os.chdir(cwd)

    real_conf = os.path.join(buildenv, 'sphinx', 'conf.py')
    print "Including real configuration file (%s)..." % (real_conf, )
    execfile(real_conf, globs, locs)

prepare(globals(), locals())
