const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { test } = require('node:test');

const scriptsDir = path.resolve(__dirname, '..');
const mampPhp = '/Applications/MAMP/bin/php/php/bin/php';
const php = process.env.TEST_PHP_BINARY || (fs.existsSync(mampPhp) ? mampPhp :
    spawnSync('/bin/bash', ['-c', 'command -v php'], { encoding: 'utf8' }).stdout.trim());
const oldPhp = process.env.TEST_OLD_PHP_BINARY;
const composerSource = `<?php
echo json_encode([
    'php' => PHP_VERSION,
    'args' => array_slice($argv, 1),
    'cwd' => getcwd(),
    'childPhp' => shell_exec('php -r ' . escapeshellarg('echo PHP_VERSION;')),
]) . "\\n";
`;

function fixture(t) {
    const root = fs.mkdtempSync(path.join(os.tmpdir(), 'elearning php-'));
    t.after(() => fs.rmSync(root, { recursive: true, force: true }));
    for (const dir of ['core/scripts', 'core/backend', 'scripts', 'stack/bin/php/selected/bin', 'stack/htdocs/app']) {
        fs.mkdirSync(path.join(root, dir), { recursive: true });
    }
    const phpBin = path.join(root, 'stack/bin/php/selected/bin/php');
    fs.symlinkSync(php, phpBin);
    fs.symlinkSync('selected', path.join(root, 'stack/bin/php/php'));
    const composer = path.join(root, 'stack/bin/php/composer');
    fs.writeFileSync(composer, composerSource);
    const helper = path.join(root, 'core/scripts/composer-update-local-php.sh');
    fs.copyFileSync(path.join(scriptsDir, 'composer-update-local-php.sh'), helper);
    const env = { ...process.env, ELEARNING_WWW_PATH: path.join(root, 'stack/htdocs/app') };
    return { root, helper, env, phpBin, composer };
}

function output(result) {
    assert.equal(result.status, 0, result.stderr || result.stdout);
    return JSON.parse(result.stdout.slice(result.stdout.indexOf('{"php"')).split('\n')[0]);
}

test('local helper prefers bundled Composer and PHP, including subprocesses', (t) => {
    const f = fixture(t);
    const result = output(spawnSync('/bin/bash', [f.helper], { env: f.env, encoding: 'utf8' }));
    assert.match(result.php, /^8\.[3-9]\./);
    assert.equal(result.childPhp, result.php);
    assert.deepEqual(result.args, ['update', '--no-interaction']);
    assert.equal(result.cwd, fs.realpathSync(path.join(f.root, 'core/backend')));
});

test('local helper forwards explicit Composer arguments', (t) => {
    const f = fixture(t);
    const result = output(spawnSync('/bin/bash', [f.helper, 'install', '--no-dev'], { env: f.env, encoding: 'utf8' }));
    assert.deepEqual(result.args, ['install', '--no-dev']);
});

test('local helper fails explicitly for missing PHP', (t) => {
    const f = fixture(t);
    fs.unlinkSync(f.phpBin);
    const result = spawnSync('/bin/bash', [f.helper], { env: f.env, encoding: 'utf8' });
    assert.notEqual(result.status, 0);
    assert.match(result.stdout, /PHP binary not found/);
});

test('XAMPP uses bundled PHP and falls back to Composer on PATH', (t) => {
    const f = fixture(t);
    fs.rmSync(path.join(f.root, 'stack/bin/php'), { recursive: true });
    fs.symlinkSync(php, path.join(f.root, 'stack/bin/php'));
    const tools = path.join(f.root, 'tools');
    fs.mkdirSync(tools);
    fs.writeFileSync(path.join(tools, 'composer'), composerSource, { mode: 0o755 });
    const result = output(spawnSync('/bin/bash', [f.helper], {
        env: { ...f.env, PATH: `${tools}:${process.env.PATH}` },
        encoding: 'utf8',
    }));
    assert.deepEqual(result.args, ['update', '--no-interaction']);
    assert.equal(result.childPhp, result.php);
});

function remoteCommand(f, args, phpBinary = f.phpBin) {
    const deploy = fs.readFileSync(path.join(scriptsDir, 'deploy.sh'), 'utf8');
    const start = deploy.indexOf('run_server_composer() {');
    const end = deploy.indexOf('\ninstall_backend_composer_packages()', start);
    assert.ok(start >= 0 && end > start);
    const source = `${deploy.slice(start, end)}
print_dryrun_msg() { printf '%s\\n' "$1"; }
ssh() { printf '%s' "\${@: -1}"; }
dryRun=false
serverPhpBinary=$1
serverComposerBinary=$2
domainRoot=$3
documentRoot=$3
serverUser=test
buildType=staging
sshIdentityFile=unused
sshUser=unused
sshHost=unused
shift 3
run_server_composer "$@"
`;
    const result = spawnSync('/bin/bash', ['-c', source, 'test', phpBinary, f.composer, f.root, ...args], { encoding: 'utf8' });
    assert.equal(result.status, 0, result.stderr);
    return result.stdout.split('\n').at(-1);
}

function runRemote(f, args, phpBinary) {
    const bin = path.join(f.root, 'mock-bin');
    fs.mkdirSync(bin);
    // Execute the generated su command without switching accounts or using SSH.
    fs.writeFileSync(path.join(bin, 'su'), '#!/bin/bash\nshift 3\nexec /bin/bash -c "$1"\n', { mode: 0o755 });
    return spawnSync('/bin/bash', ['-c', remoteCommand(f, args, phpBinary)], {
        env: { ...process.env, PATH: `${bin}:${process.env.PATH}` },
        encoding: 'utf8',
    });
}

test('server Composer uses explicit PHP, keeps arguments and subprocess runtime', (t) => {
    const f = fixture(t);
    fs.mkdirSync(path.join(f.root, 'backend'));
    const result = output(runRemote(f, ['update', '--no-interaction', '--no-dev']));
    assert.deepEqual(result.args, [`--working-dir=${f.root}/backend`, 'update', '--no-interaction', '--no-dev']);
    assert.equal(result.childPhp, result.php);
});

test('server preflight does not require a deployed backend directory', (t) => {
    const f = fixture(t);
    const result = output(runRemote(f, ['--version']));
    assert.deepEqual(result.args, ['--version']);
});

test('server missing PHP fails explicitly', (t) => {
    const f = fixture(t);
    const result = runRemote(f, ['--version'], path.join(f.root, 'missing'));
    assert.notEqual(result.status, 0);
    assert.match(result.stderr, /Server PHP or Composer not found/);
});

test('server missing Composer fails explicitly', (t) => {
    const f = fixture(t);
    fs.unlinkSync(f.composer);
    const result = runRemote(f, ['--version']);
    assert.notEqual(result.status, 0);
    assert.match(result.stderr, /Server PHP or Composer not found/);
});

test('PHP 7.4 is rejected locally and remotely before Composer', { skip: !oldPhp }, (t) => {
    const f = fixture(t);
    fs.unlinkSync(f.phpBin);
    fs.symlinkSync(oldPhp, f.phpBin);
    const local = spawnSync('/bin/bash', [f.helper], { env: f.env, encoding: 'utf8' });
    assert.notEqual(local.status, 0);
    assert.match(local.stderr, /requires PHP >=8.3/);
    assert.doesNotMatch(local.stdout, /{"php"/);
    const remote = runRemote(f, ['--version']);
    assert.notEqual(remote.status, 0);
    assert.match(remote.stderr, /Server CLI PHP must be >=8.3/);
    assert.doesNotMatch(remote.stdout, /{"php"/);
});
