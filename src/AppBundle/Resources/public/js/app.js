var minigram = {
    page: 1,
    init: function( ) {
        this.imageSubmitEvent();
        this.getStats();
        this.events();
    },
    imageSubmitEvent: function( ) {
        document.getElementById("post_image").onchange = function() {
            document.getElementById("upload-form").submit();
        };
    },
    events: function() {
        window.setInterval(function(){
            minigram.getStats();
        }, 15000);

        window.onscroll = function(ev) {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
                minigram.getPostsPaginated(minigram.page);
            }
        };
    },
    getStats: function( ) {
        var request = new XMLHttpRequest();
        request.open('GET', Routing.generate('ajax_stats'), true);

        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                var data = JSON.parse(request.responseText);

                document.getElementById('post_count').innerHTML = data.posts_count;
                document.getElementById('views_count').innerHTML = data.views_count;
            }
        };

        request.onerror = function() {};
        request.send();
    },
    getPostsPaginated: function( ) {
        var request = new XMLHttpRequest();
        request.open('GET', Routing.generate('ajax_post_list') + '?page=' + minigram.page, true);

        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                var data = request.responseText;

                var div = document.getElementById('post_listing');
                div.innerHTML = div.innerHTML + data;
            }
        };

        request.onerror = function() {};
        request.send();

        minigram.page = minigram.page + 1;
    }
};

minigram.init();
