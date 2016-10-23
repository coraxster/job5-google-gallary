function initGallery() {
    $('.container').magnificPopup({
        delegate: 'img',
        type: 'image',
        gallery:{
            enabled:true,
            preload: [2,2],
            navigateByImgClick: true,
        },
        mainClass: 'mfp-with-zoom',
        zoom: {
            enabled: true,
            duration: 300,
            easing: 'ease-in-out',
            opener: function(openerElement) {
                return openerElement.is('img') ? openerElement : openerElement.find('img');
            }
        },
        image: {
            titleSrc: 'id'
        },
        callbacks: {
            change: function() {
                var container = $('.container');
                var id = $(this.content).find('.mfp-title').text();
                var current_th = $(container).find('#'+id);
                var scroll_to =
                    $(container).scrollLeft()
                    - $(container).offset().left
                    + $(current_th).offset().left
                    - ( $(container).width() / 2 - ($(current_th).width() / 2));

                $(container).animate({
                    scrollLeft:  scroll_to
                });

            },
        }
    });
}





$('button').click(function (e) {
    $('form').hide();
    query = $('[name = "query"]').val();
    $('.container').html('<div class="loading"><h2>Loading... Query: '+ query +'</h2></div>');
    $.post( "/search", { query: query})
        .fail(function(xhr, status, error) {
            if (typeof data.error !== 'undefined') {
                alert(data.error);
            }else{
                alert('error, see logs');
            }
        })
        .done(function( data ) {
            if (typeof data.error !== 'undefined') {
                alert(data.error);
                return
            }
            $('.container').html('');
            data.images.forEach(function(entry, i) {
                $('.container').append('<img src="'+entry.th+'" data-mfp-src="'+entry.big+'" width="240" class="th" id="image_'+i+'"> ');
            });
            initGallery();
        });

    e.preventDefault();
});