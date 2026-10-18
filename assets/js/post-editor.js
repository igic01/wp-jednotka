/* The image category picker is supplied by the sidebar meta box. */
wp.domReady(function () {
  const editor = wp.data.dispatch('core/editor');
  if (editor && typeof editor.removeEditorPanel === 'function') {
    editor.removeEditorPanel('taxonomy-panel-category');
  } else {
    wp.data.dispatch('core/edit-post').removeEditorPanel('taxonomy-panel-category');
  }
});
