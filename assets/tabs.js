// Story tabs + category filtering. Framework-free; works with the renderer's
// Bootstrap-style tab markup, so the same HTML runs on the static site.
document.addEventListener('click', function (e) {

  // Category filter (sidebar / chips).
  var filter = e.target.closest('[data-filter]');
  if (filter) {
    e.preventDefault();
    var value = filter.getAttribute('data-filter');
    document.querySelectorAll('[data-filter]').forEach(function (link) {
      link.classList.toggle('active', link === filter);
    });
    document.querySelectorAll('.category[data-category]').forEach(function (section) {
      section.hidden = value !== '__all__' && section.getAttribute('data-category') !== value;
    });
    return;
  }

  // Story tabs.
  var link = e.target.closest('[data-bs-toggle="tab"]');
  if (link) {
    e.preventDefault();
    var pane = document.querySelector(link.getAttribute('href'));
    if (!pane) return;
    var nav = link.closest('.nav');
    var content = pane.parentElement;
    nav.querySelectorAll('.nav-link').forEach(function (l) { l.classList.remove('active'); });
    content.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active'); });
    link.classList.add('active');
    pane.classList.add('active');
  }
});
