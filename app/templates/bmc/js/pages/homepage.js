$(document).ready(fucntion() {

  $(window).on('load', function() {

    var intro = $('.intro-container'),
        tl = new TimelineMax();

    tl
      .from(intro, 1, {y:-15, autoAlpha:0, ease:Power1.easeOut});

  });

});
