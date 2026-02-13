/* Lightweight UI delegation + feature detection for dashboard
   - Delegates clicks for elements with `data-action="name[:arg]"`
   - Ensures legacy globals exist (no-op fallbacks when needed)
   - Exports `init` for unit tests
*/
(function () {
  'use strict';

  // noop helpers used as safe fallbacks
  function noop() {}

  // Names of critical handlers we expect on the page
  const CRITICAL_HANDLERS = [
    'showSection',
    'toggleSubmenu',
    'scrollToCRM',
    'openAddClientModal',
    'closeAddClientModal',
    'saveNewClient',
    'loadExistingClients'
  ];

  // Ensure a named function exists on window; attach fallback if missing
  function ensureGlobal(name, fallback) {
    if (typeof window[name] === 'undefined') {
      window[name] = fallback || noop;
    }
    return window[name];
  }

  // Parse a data-action value: 'name:arg1,arg2' -> { name, args }
  function parseAction(actionStr) {
    if (!actionStr) return null;
    const [name, rawArgs] = actionStr.split(':');
    const args = rawArgs ? rawArgs.split(',').map(a => a.trim()) : [];
    return { name, args };
  }

  // Delegated click handler for elements with data-action
  function delegatedClickHandler(ev) {
    const el = ev.target.closest && ev.target.closest('[data-action]');
    if (!el) return;
    const act = el.getAttribute('data-action');
    const parsed = parseAction(act);
    if (!parsed) return;

    const fn = window[parsed.name];
    // Debug: report delegated actions to console for easier diagnosis
    // eslint-disable-next-line no-console
    console.debug(`[dashboard-ui] delegated action -> ${parsed.name}`, { element: el, args: parsed.args });
    if (typeof fn === 'function') {
      try {
        // Prefer explicit data-params attribute when present (single raw arg)
        const explicitParam = el.getAttribute('data-params');
        const args = explicitParam !== null ? [explicitParam] : parsed.args;

        // For toggleSubmenu we intentionally pass the event as first arg
        if (parsed.name === 'toggleSubmenu') {
          fn(ev, ...(args));
        } else {
          fn(...args);
        }
      } catch (err) {
        // swallow errors here but log for debugging
        // eslint-disable-next-line no-console
        console.error('dashboard-ui delegated handler error', parsed.name, err);
      }
    }

    // Prevent default if element is an anchor or button
    if (el.tagName === 'A' || el.tagName === 'BUTTON') ev.preventDefault();
  }

  // Attach delegation and expose safe globals
  function init() {
    // 1) ensure fallback globals exist so tests / inline attributes don't break
    CRITICAL_HANDLERS.forEach(name => ensureGlobal(name, noop));

    // 2) attach delegated click handler once
    if (!document._dashboardUiBound) {
      document.addEventListener('click', delegatedClickHandler, true);
      document._dashboardUiBound = true;
    }

    // 3) attach a small keystroke for adding client (Ctrl/Cmd+N)
    if (!document._dashboardUiKeybinds) {
      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key && e.key.toLowerCase() === 'n') {
          const fn = window.openAddClientModal;
          if (typeof fn === 'function') fn();
        }
      });
      document._dashboardUiKeybinds = true;
    }
  }

  // Auto-init in browsers
  if (typeof window !== 'undefined' && typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }
  }

  // Export for tests / CommonJS
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { init, ensureGlobal, parseAction };
  } else {
    window.DashboardUI = { init, ensureGlobal, parseAction };
  }
})();