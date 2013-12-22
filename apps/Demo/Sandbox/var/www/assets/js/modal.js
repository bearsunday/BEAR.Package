var MyDialogs = {
    loadConfirmationModal:function (modalId, confirmURL, caption, body) {
        var $modal = jQuery('#' + modalId);
        if ($modal.size() === 0) {
            var modalString = '<div id="'
                + modalId
                + '" class="modal fade" role="dialog" tabindex="-1" aria-hidden="true">'
                + '<div class="modal-dialog"><div class="modal-content">'
                + '<div class="modal-header">'
                + '<button class="close" data-dismiss="modal" aria-hidden="true">&times;</button>'
                + '<h3 class="modal-title">'
                + caption
                + '</h3>'
                + '</div>'
                + '<div class="modal-body">'
                + body
                + '</div>'
                + '<div class="modal-footer">'
                + '<button id="cancel" class="btn" data-dismiss="modal" type="button" name="cancel">Cancel</button>'
                + '<a id="submit" class="btn btn-danger" href="'
                + confirmURL + '">Delete</a>' + '</div></div></div></div>';
            console.log(modalString);
            $modal = jQuery(modalString);
        }
        $modal.modal('show');
        return false;
    }
};
