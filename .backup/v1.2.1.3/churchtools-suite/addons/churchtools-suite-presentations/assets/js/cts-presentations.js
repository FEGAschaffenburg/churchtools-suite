(function () {
  "use strict";

  function initSlider(root) {
    var slides = Array.prototype.slice.call(root.querySelectorAll('.cts-presentation-slide'));
    if (!slides.length) {
      return;
    }

    var dotsContainer = root.querySelector('.cts-presentation-dots');
    var prevBtn = root.querySelector('[data-cts-prev]');
    var nextBtn = root.querySelector('[data-cts-next]');
    var seconds = parseInt(root.getAttribute('data-slide-seconds') || '10', 10);
    if (!seconds || seconds < 3) {
      seconds = (window.ctsPresentationsConfig && window.ctsPresentationsConfig.defaultSeconds) || 10;
    }

    var index = 0;
    var timer = null;

    function renderDots() {
      if (!dotsContainer) {
        return;
      }
      dotsContainer.innerHTML = '';
      slides.forEach(function (_slide, i) {
        var dot = document.createElement('span');
        dot.className = 'cts-presentation-dot' + (i === index ? ' is-active' : '');
        dotsContainer.appendChild(dot);
      });
    }

    function show(nextIndex) {
      index = (nextIndex + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === index);
      });
      renderDots();
    }

    function restartTimer() {
      if (timer) {
        window.clearInterval(timer);
      }
      timer = window.setInterval(function () {
        show(index + 1);
      }, seconds * 1000);
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        show(index - 1);
        restartTimer();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        show(index + 1);
        restartTimer();
      });
    }

    show(0);
    restartTimer();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var sliders = document.querySelectorAll('.cts-presentation');
    sliders.forEach(initSlider);
  });
})();
