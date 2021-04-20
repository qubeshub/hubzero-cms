window.addEventListener("DOMContentLoaded", function () {
    // get the sticky element
    const stickyElm = document.querySelector('.title-wrapper')

    const observer = new IntersectionObserver(
        ([e]) => e.target.classList.toggle('isSticky', e.intersectionRatio < 1),
        {
            rootMargin: '-56.36px 0px 0px 0px',
            threshold: [1]
        }
    );
    

    observer.observe(stickyElm)
        
})

