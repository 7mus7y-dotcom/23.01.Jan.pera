(function ($) {
  var originalEdit = inlineEditPost.edit;

  inlineEditPost.edit = function (id) {
    originalEdit.apply(this, arguments);

    var postId = typeof id === 'object' ? this.getId(id) : id;
    if (!postId) {
      return;
    }

    var $row = $('#post-' + postId);
    var districtId = parseInt($row.find('.pera-district-term').data('district-id'), 10) || 0;
    $('#edit-' + postId)
      .find('select[name="pera_district_term"]')
      .val(districtId);
  };
})(jQuery);
