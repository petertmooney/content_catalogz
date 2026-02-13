/** @jest-environment jsdom */

const { init, ensureGlobal, parseAction } = require('../assets/js/dashboard-ui.js');

describe('dashboard-ui delegation & globals', () => {
  beforeEach(() => {
    // reset document body
    document.body.innerHTML = '';
  });

  test('exports init and helper functions', () => {
    expect(typeof init).toBe('function');
    expect(typeof ensureGlobal).toBe('function');
    expect(typeof parseAction).toBe('function');
  });

  test('ensures critical globals exist as no-op when missing', () => {
    // pick a dummy name
    ensureGlobal('someMissingFn');
    expect(typeof window.someMissingFn).toBe('function');
  });

  test('parseAction splits name and args', () => {
    const p = parseAction('showSection:dashboard');
    expect(p).toEqual({ name: 'showSection', args: ['dashboard'] });

    const p2 = parseAction('openAddClientModal');
    expect(p2).toEqual({ name: 'openAddClientModal', args: [] });
  });

  test('delegated click calls global handler', () => {
    // provide a spy global
    window.openAddClientModal = jest.fn();

    // create an element with data-action
    const btn = document.createElement('button');
    btn.setAttribute('data-action', 'openAddClientModal');
    document.body.appendChild(btn);

    init(); // attach delegation

    // simulate click
    btn.click();

    expect(window.openAddClientModal).toHaveBeenCalled();
  });

  test('data-params passes single raw argument to handler', () => {
    window.runQuickQuery = jest.fn();
    const b = document.createElement('button');
    b.setAttribute('data-action', 'runQuickQuery');
    b.setAttribute('data-params', "SELECT status, COUNT(*) as count FROM quotes GROUP BY status");
    document.body.appendChild(b);

    init();
    b.click();
    expect(window.runQuickQuery).toHaveBeenCalledWith("SELECT status, COUNT(*) as count FROM quotes GROUP BY status");
  });

  test('delegation passes argument to handler', () => {
    window.showSection = jest.fn();
    const a = document.createElement('a');
    a.setAttribute('data-action', 'showSection:existing-clients');
    document.body.appendChild(a);

    init();
    a.click();
    expect(window.showSection).toHaveBeenCalledWith('existing-clients');
  });

  test('toggleSubmenu receives event and arg', () => {
    window.toggleSubmenu = jest.fn();
    const el = document.createElement('a');
    el.setAttribute('data-action', 'toggleSubmenu:clients-submenu');
    document.body.appendChild(el);

    init();
    const clickEvent = new MouseEvent('click', { bubbles: true });
    el.dispatchEvent(clickEvent);

    // first argument should be an event object
    expect(window.toggleSubmenu.mock.calls[0][0] instanceof Event).toBe(true);
    expect(window.toggleSubmenu).toHaveBeenCalledWith(expect.any(Event), 'clients-submenu');
  });
});