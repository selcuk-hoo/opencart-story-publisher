// Tiny tab switcher. Works with the renderer's Bootstrap-style markup
// (a.nav-link[data-bs-toggle="tab"][href="#pane-id"]), so the same HTML runs
// on the static site with no framework.
document.addEventListener('click', function (e) {
  var link = e.target.closest('[data-bs-toggle="tab"]');
  if (!link) return;
  e.preventDefault();

  var pane = document.querySelector(link.getAttribute('href'));
  if (!pane) return;

  var nav = link.closest('.nav');
  var content = pane.parentElement;

  nav.querySelectorAll('.nav-link').forEach(function (l) { l.classList.remove('active'); });
  content.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active'); });

  link.classList.add('active');
  pane.classList.add('active');
});
