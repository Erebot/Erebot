# -*- coding: utf-8 -*-

import glob
import os.path

pkg_name = 'Erebot'
pkg_version = '0.3.0'
pkg_email = 'clicky@erebot.net'

def irglob(dirname, pattern):
    it = glob.iglob('%s/%s' % (dirname, pattern))
    while True:
        try:
            yield it.next()
        except StopIteration:
            break

    it = glob.iglob('%s/*/' % dirname)
    for d in it:
        for f in irglob(d, pattern):
            yield f

sources = list(irglob('src/', '*.php'))
POT = Glob('i18n/*.pot')[0]
POs = Glob('i18n/*/LC_MESSAGES/*.po')
MOs = [os.path.splitext(str(n))[0] + '.mo' for n in POs]


env = Environment()

for tool in (
        'xgettext',
        'msgfmt',
        'msginit',
        'msgmerge',
        'phpunit',
        'doxygen',
        'make',
    ):
    env[tool] = env.WhereIs(tool)

def init_catalog(target, source, env):
    destdir = Dir('i18n/%s/LC_MESSAGES/' % ARGUMENTS['catalog'])
    if not os.path.exists(str(destdir)):
        Execute(Mkdir(destdir))
    Execute(env.Action(
        'msginit --no-translator -w 80 -l %(catalog)s '
        '-i %(source)s -o %(dest)s' % {
            'catalog': ARGUMENTS['catalog'],
            'dest': destdir.File('%s.po' % pkg_name),
            'source': source[0],
        }
    ))

def extract_messages(target, source, env, for_signature):
    return (
        '$xgettext -o $TARGET '
        '--from-code utf-8 --foreign-user --no-location '
        '-E -i -w 80 -s -L PHP --omit-header --strict '
        '--package-name %(name)s --package-version %(version)s '
        '--msgid-bugs-address %(email)s %(sources)s' % {
            'name': pkg_name,
            'version': pkg_version,
            'email': pkg_email,
            'sources': ' '.join(sources),
        }
    )

def update_catalog(target, source, env, for_signature):
    return (
        '$msgmerge --backup=off -U -N -e -i --strict -w 80 '
        '--add-location -s -q --no-location $TARGET $SOURCE' % {
            'source': source[0],
            'target': target[0],
        }
    )

def compile_catalog(target, source, env, for_signature):
    return [
        '$msgfmt -f -o %(target)s %(source)s' % {
            'target': target[i],
            'source': source[i],
        }
        for i in xrange(len(source))
    ]

env['BUILDERS']['ExtractMessages'] =    Builder(generator=extract_messages)
env['BUILDERS']['UpdateCatalog'] =      Builder(generator=update_catalog, suffix='.po', src_suffix='.pot', single_source=True)
env['BUILDERS']['CompileCatalog'] =     Builder(generator=compile_catalog, src_suffix='.po', suffix='.mo', single_source=True)


# Documentation
env.Command('doc/latex/refman.pdf', ['doc/latex/Makefile'], env.Action('$make', chdir=1))
env.Command(['doc/html', 'doc/latex'], ['Doxyfile'] + sources, '$doxygen')
env.Alias('doc', 'doc/html')
env.Alias('refman', ['doc/latex/refman.pdf'])

# Unit tests
env.Command('test', ['phpunit.xml'], '$phpunit')
env.AlwaysBuild('test')

# I18N
env.ExtractMessages(POT, sources)
env.Alias('extract_messages', POT)
env.UpdateCatalog(POs, POT)
env.Alias('update_catalog', POs)
i18n = env.CompileCatalog(source=POs)
env.Alias('compile_catalog', MOs)
env.Command('init_catalog', [POT], env.Action(init_catalog))
env.AlwaysBuild('init_catalog')
env.NoClean(POT, *POs)
env.Precious(POT, *POs)

Default(i18n)

