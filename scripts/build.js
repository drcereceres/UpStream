const exec = require('child_process').exec;
const p = require('path');

// Load the package.json file.
const packageJSON = require('../package.json');
const rootPath = p.dirname(p.dirname(__filename));

if (!packageJSON.name) {
    return console.log('Missing "name" parameter.');
}

if (!packageJSON.version) {
    return console.log('Missing "version" parameter.');
}

const PKG_NAME = 'UpStream';
const FOLDER_NAME = 'upstream';
const buildCommandParams = `"${PKG_NAME}" "${FOLDER_NAME}" "${packageJSON.version}" ${rootPath}/builds`;

const child = exec(`sh ${__dirname}/build.sh ${buildCommandParams}`, function (error, stdout, stderr) {
    if (error) {
        return console.log(error);
    }

    console.log(stdout);
});
