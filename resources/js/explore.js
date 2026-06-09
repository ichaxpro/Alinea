// Hero Carousel Logic
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('.hero-dot');
const totalSlides = slides.length;
let slideInterval;

function updateSlider(newIndex) {
    if (totalSlides <= 1) return;
    
    slides[currentSlide].classList.remove('opacity-100', 'z-10', 'scale-100', 'relative');
    slides[currentSlide].classList.add('opacity-0', 'z-0', 'scale-[0.98]', 'absolute', 'inset-0');
    dots[currentSlide].classList.remove('w-8', 'bg-[#444]');
    dots[currentSlide].classList.add('w-2.5', 'bg-gray-300');
    
    currentSlide = newIndex;
    
    slides[currentSlide].classList.remove('opacity-0', 'z-0', 'scale-[0.98]', 'absolute', 'inset-0');
    slides[currentSlide].classList.add('opacity-100', 'z-10', 'scale-100', 'relative');
    dots[currentSlide].classList.remove('w-2.5', 'bg-gray-300');
    dots[currentSlide].classList.add('w-8', 'bg-[#444]');
}

function nextSlide() {
    let next = (currentSlide + 1) % totalSlides;
    updateSlider(next);
}

window.goToSlide = function(index) {
    if (index === currentSlide) return;
    clearInterval(slideInterval);
    updateSlider(index);
    startSlider(); // Restart interval after manual interaction
};

function startSlider() {
    if (totalSlides > 1) {
        slideInterval = setInterval(nextSlide, 5000); // 5 seconds interval
    }
}

// Initialize Slider
if (totalSlides > 1) {
    startSlider();
}

// Row Scroll Logic
window.scrollRow = function(rowId, direction) {
    const row = document.getElementById(rowId);
    if (!row) return;
    
    // Scroll by roughly 80% of the visible container width
    const scrollAmount = row.clientWidth * 0.8;
    if (direction === 'left') {
        row.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        row.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
};
