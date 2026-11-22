const { afterEach, beforeEach, expect, jest: jestApi, test } = require('@jest/globals');

let app;
let browser;
let elements;
let results;
let request;

beforeEach(() => {
    elements = Object.fromEntries(
        ['loginName', 'loginPassword', 'loginResult', 'colorText', 'colorAddResult',
            'searchText', 'colorSearchResult'].map(id => [id, { value: '', innerHTML: '' }]),
    );
    results = { innerHTML: '' };
    request = { open: jestApi.fn(), setRequestHeader: jestApi.fn(), send: jestApi.fn() };
    browser = {
        document: {
            cookie: '',
            getElementById: id => elements[id],
            getElementsByTagName: tag => tag === 'p' ? [results] : [],
        },
        window: { location: { href: '' } },
        XMLHttpRequest: jestApi.fn(function () {
            return request;
        }),
    };
    Object.assign(globalThis, browser);
    jestApi.isolateModules(() => {
        app = require('../../src/js/code.js');
    });
});

afterEach(() => {
    for (const name of Object.keys(browser)) {
        delete globalThis[name];
    }
});

function respond(payload) {
    request.readyState = 4;
    request.status = 200;
    request.responseText = JSON.stringify(payload);
    request.onreadystatechange();
}

function restoreSession() {
    browser.document.cookie = 'firstName=Ada,lastName=Lovelace,userId=7,token=test-token';
    app.readCookie();
}

test('login posts credentials, saves the returned session, and opens the dashboard', () => {
    elements.loginName.value = 'ada';
    elements.loginPassword.value = 'example-password';

    app.doLogin();

    expect(request.open).toHaveBeenCalledWith('POST', './api/login.php', true);
    expect(JSON.parse(request.send.mock.calls[0][0])).toEqual({
        login: 'ada',
        password: 'example-password',
    });
    respond({ id: 7, firstName: 'Ada', lastName: 'Lovelace', token: 'test-token' });

    expect(browser.document.cookie).toMatch(/^firstName=Ada,lastName=Lovelace,userId=7,token=test-token;expires=/);
    expect(browser.window.location.href).toBe('./color.html');
});

test('opening the dashboard without a session redirects to login', () => {
    app.readCookie();

    expect(browser.window.location.href).toBe('index.html');
});

test('logout expires the session cookie and redirects to login', () => {
    restoreSession();

    app.doLogout();

    expect(browser.document.cookie).toContain('01 Jan 1970');
    expect(browser.window.location.href).toBe('index.html');
});

test('adding a color sends the restored session and displays confirmation', () => {
    restoreSession();
    elements.colorText.value = 'Blue';

    app.addColor();

    expect(request.open).toHaveBeenCalledWith('POST', './api/add_color.php', true);
    expect(JSON.parse(request.send.mock.calls[0][0])).toEqual({
        color: 'Blue',
        userId: 7,
        token: 'test-token',
    });
    respond({ error: '' });
    expect(elements.colorAddResult.innerHTML).toBe('Color has been added');
});

test('searching sends the query and renders the returned colors in order', () => {
    restoreSession();
    elements.searchText.value = 'bl';

    app.searchColor();

    expect(request.open).toHaveBeenCalledWith('POST', './api/search_colors.php', true);
    expect(JSON.parse(request.send.mock.calls[0][0])).toEqual({
        search: 'bl',
        userId: 7,
        token: 'test-token',
    });
    respond({ results: ['Black', 'Blue'], error: '' });
    expect(results.innerHTML).toBe('Black<br />\r\nBlue');
    expect(elements.colorSearchResult.innerHTML).toBe('Color(s) has been retrieved');
});
