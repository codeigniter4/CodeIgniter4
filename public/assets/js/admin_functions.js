$(window).resize(function () {
    var footerHeight = $('.footer').outerHeight();
    var stickFooterPush = $('.push').height(footerHeight);
    $('.wrapper').css({'marginBottom': '-' + footerHeight + 'px'});
});

$(document).ready(function () {   

    $('[data-toggle="ajaxModal"]').click( function (e) {
        $('#ajaxModal').remove();
        e.preventDefault();
        var $this = $(this),
                $remote = $this.data('remote') || $this.attr('href'), $modal = $('<div class="modal" id="ajaxModal"><div class="modal-body"></div></div>');
        $('body').append($modal);
        $modal.modal();
        $modal.load($remote);
    });

    $(window).resize();

});

