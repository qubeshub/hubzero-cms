$(document).ready(function() {

  // Display active filter status

  var $sortBy = $('.sort-option li'),
      $viewOption = $('.view-option li'),
      $imgOption = $('.img-option li');


  $sortBy.on('click', function() {
    $(this).addClass('active');
    $sortBy.not(this).removeClass('active');
  });

  $viewOption.click(function () {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');

    var activeIndex = $(this).index() + 1;
    localStorage.setItem('mySelectValue', activeIndex);
  });

  var activeIndex = localStorage.getItem('mySelectValue');
  if (isNaN(activeIndex)) {
       console.log('nothing stored');
      }
  else {
        $('.view-option li').removeClass('active');
        $('.view-option li:nth-child(' + activeIndex + ')').addClass('active');
      }

  $imgOption.on('click', function() {
    $(this).addClass('active');
    $imgOption.not(this).removeClass('active');
  });

  // Check if image exists in list view and make necessary adjustments for mobile

  var $resourceImg = $('.resource-info-wrapper img'),
      $infoWrapper = $('.resource-info-wrapper'),
      $info = $('.resource-info');

  $(window).on('resize', function() {
    var $windowSize = $(window).width();

    if ($infoWrapper.has('img') && $windowSize < 1101) {
      console.log('detected!');
      $infoWrapper.css({'flex-wrap': 'wrap'});
      $resourceImg.css({'width': '100%'});
      $info.css({'width': '100%'});
    } else {
      console.log('too big!');
      $infoWrapper.css({'flex-wrap': 'nowrap'});
      $resourceImg.css({'width': 'auto'});
      $info.css({'width': 'auto'});
    }

  });
});
