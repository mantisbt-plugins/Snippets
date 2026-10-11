// Run with: node --test tests/rest-api-path.test.js (requires PHP and Node.js 18+).
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../files/snippets.js'), 'utf8');
const render = (...args) => execFileSync('php', [path.join(__dirname, 'fixtures/resources.php'), ...args], { encoding: 'utf8' });

function requests(resources, bugId) {
	const match = resources.match(/id="snippets-script"[^>]*data-rest-api-path="([^"]*)"/);
	assert.ok(match, 'The script must carry the configured API path');
	const apiPath = match[1].replace(/&(?:amp|quot|#039|lt|gt);/g, entity => ({
		'&amp;': '&', '&quot;': '"', '&#039;': "'", '&lt;': '<', '&gt;': '>'
	})[entity]);
	const urls = [];
	const noop = { on() {} };
	function jquery(selector) {
		if (selector === '#snippets-script') return { attr: () => apiPath };
		if (selector === 'textarea') return { not: () => ({ length: 1 }) };
		if (selector === 'input[name="bug_id"]') return { val: () => bugId };
		if (selector === '.snippetspatternhelp') return { length: 1 };
		return noop;
	}
	const response = { done() { return this; }, fail() { return this; } };
	jquery.getJSON = jquery.get = url => { urls.push(url); return response; };
	vm.runInNewContext(source, { jQuery: callback => callback(jquery) });
	return urls;
}

test('preserves the default URLs on installations without rewriting', () => {
	assert.deepEqual(requests(render(), undefined), [
		'api/rest/index.php/plugins/Snippets/data',
		'api/rest/index.php/plugins/Snippets/help'
	]);
});

for (const apiPath of ['api/rest/plugins/Snippets/', 'api/rest/plugins/Snippets', '/mantisbt/api/rest/plugins/Snippets/']) {
	test(`uses ${apiPath} for issue data and pattern help`, () => {
		const base = apiPath.replace(/\/+$/, '') + '/';
		assert.deepEqual(requests(render(apiPath), 123), [base + 'data/123', base + 'help']);
	});
}

test('escapes the API path in HTML and preserves its value in JavaScript', () => {
	const apiPath = '/mantisbt/"quoted"&path/plugins/Snippets/';
	const resources = render(apiPath);
	assert.match(resources, /data-rest-api-path="\/mantisbt\/&quot;quoted&quot;&amp;path\/plugins\/Snippets\/"/);
	assert.deepEqual(requests(resources, 123), [apiPath + 'data/123', apiPath + 'help']);
});
