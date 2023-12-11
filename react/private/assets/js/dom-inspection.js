jQuery(document).ready(function ($) {
  var hasDOMElements = $(PluginDOMSettings.element_identifier).length > 0;

  $.post(PluginDOMSettings.ajax_url, {
    action: 'report_dom_inspection',
    hasDOMElements: hasDOMElements,
  });
});
