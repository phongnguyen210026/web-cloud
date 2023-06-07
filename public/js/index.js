function isElementInViewport(element) {
    var rect = element.getBoundingClientRect();
    var windowHeight = (window.innerHeight || document.documentElement.clientHeight);
    return (rect.top <= windowHeight * 0.8);
  }
  
  function animateOnScroll() {
    var articles = document.querySelectorAll('.article');
  
    articles.forEach(function (article) {
      if (isElementInViewport(article) && !article.classList.contains('animate')) {
        article.classList.add('animate');
        article.querySelector('.text-article').classList.add('animate');
        article.querySelector('.image-article').classList.add('animate');
      }
    });

    // Remove the event listener if all articles have been animated
    if (document.querySelectorAll('.article.animate').length === articles.length) {
      window.removeEventListener('scroll', animateOnScroll);
    }
  }
  
  // Attach the event listener to trigger the animation on scroll
  window.addEventListener('scroll', animateOnScroll);