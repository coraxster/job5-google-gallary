








$('button').click(function () {
    query = $('[name = "query"]').val();
    console.log(query);
    $.post( "/search", { query: query})
      .done(function( data ) {
         if (typeof data.error !== 'undefined') {
           alert(data.error);
           return
         }
         $('.result').html('');
         data.images.forEach(function(entry) {
            $('.result').append('<img src="/'+entry+'" width="240"><br/>');
        });
      });
});