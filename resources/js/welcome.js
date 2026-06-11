               SCROLL PROGRESS BAR
            const progressBar = document.getElementById('scroll-progress');
            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                if (progressBar) progressBar.style.width = progress + '%';
            }, { passive: true });

               SMOOTH SCROLL
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href === '#' || !href) return;
                    e.preventDefault();
                    const el = document.querySelector(href);
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                    if (typeof window.closeMobileMenu === 'function') {
                        window.closeMobileMenu();
                    }
                });
            });

               SCROLL REVEAL (IntersectionObserver)
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
                revealObserver.observe(el);
            });

               TYPEWRITER EFFECT
            const phrases = [
                'Pinjam. Baca. Bagikan.',
                'Temukan Buku Favoritmu.',
                'Komunitas Pembaca Indonesia.',
                'Explore. Review. Repeat.'
            ];
            let phraseIndex = 0, charIndex = 0, isDeleting = false;
            const typeEl = document.getElementById('typewriter-text');

            function typeWriter() {
                if (!typeEl) return;
                const current = phrases[phraseIndex];
                
                if (current === 'Explore. Review. Repeat.') {
                    typeEl.classList.add('italic');
                } else {
                    typeEl.classList.remove('italic');
                }

                if (isDeleting) {
                    typeEl.textContent = current.substring(0, charIndex--);
                    if (charIndex < 0) {
                        isDeleting = false;
                        phraseIndex = (phraseIndex + 1) % phrases.length;
                        setTimeout(typeWriter, 400);
                        return;
                    }
                    setTimeout(typeWriter, 50);
                } else {
                    typeEl.textContent = current.substring(0, charIndex++);
                    if (charIndex > current.length) {
                        isDeleting = true;
                        setTimeout(typeWriter, 1800);
                        return;
                    }
                    setTimeout(typeWriter, 80);
                }
            }
            setTimeout(typeWriter, 800);

               ANIMATED COUNTERS
            function animateCounter(el) {
                const target = parseInt(el.dataset.count);
                const suffix = el.dataset.suffix || '';
                const duration = 1800;
                const step = target / (duration / 16);
                let current = 0;
                const timer = setInterval(() => {
                    current = Math.min(current + step, target);
                    el.textContent = Math.floor(current).toLocaleString('id-ID') + suffix;
                    if (current >= target) clearInterval(timer);
                }, 16);
            }

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

               TAG PILLS TOGGLE
            document.querySelectorAll('.tag-pill').forEach(pill => {
                pill.addEventListener('click', () => {
                    document.querySelectorAll('.tag-pill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                });
            });

               PARALLAX BLOBS
            const blobs = document.querySelectorAll('.parallax-blob');
            window.addEventListener('mousemove', (e) => {
                const cx = window.innerWidth / 2, cy = window.innerHeight / 2;
                const dx = (e.clientX - cx) / cx;
                const dy = (e.clientY - cy) / cy;
                blobs.forEach(blob => {
                    const speed = parseFloat(blob.dataset.speed || 0.04);
                    blob.style.transform = `translate(${dx * speed * 80}px, ${dy * speed * 80}px)`;
                });
            }, { passive: true });

               PARTICLE CANVAS
            const canvas = document.getElementById('particle-canvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                let particles = [];

                function resizeCanvas() {
                    const hero = document.getElementById('hero-section');
                    if (!hero) return;
                    canvas.width = hero.offsetWidth;
                    canvas.height = hero.offsetHeight;
                }
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas, { passive: true });

                function createParticle() {
                    return {
                        x: Math.random() * canvas.width,
                        y: canvas.height + 10,
                        r: Math.random() * 3 + 1,
                        vy: -(Math.random() * 0.6 + 0.2),
                        vx: (Math.random() - 0.5) * 0.4,
                        life: 1,
                        decay: Math.random() * 0.004 + 0.003,
                        color: Math.random() > 0.5 ? '#fbbf24' : '#7dd3fc'
                    };
                }

                for (let i = 0; i < 30; i++) {
                    const p = createParticle();
                    p.y = Math.random() * canvas.height;
                    p.life = Math.random();
                    particles.push(p);
                }

                function drawParticles() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    particles.forEach((p, i) => {
                        p.x += p.vx;
                        p.y += p.vy;
                        p.life -= p.decay;
                        if (p.life <= 0) particles[i] = createParticle();
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                        ctx.fillStyle = p.color;
                        ctx.globalAlpha = p.life * 0.5;
                        ctx.fill();
                    });
                    ctx.globalAlpha = 1;
                    requestAnimationFrame(drawParticles);
                }
                drawParticles();
            }

               REVIEW CARD TILT EFFECT (mouse parallax)
            document.querySelectorAll('.review-card').forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;
                    const rx = ((e.clientY - cy) / (rect.height / 2)) * 4;
                    const ry = -((e.clientX - cx) / (rect.width / 2)) * 4;
                    card.style.transform = `rotate(0deg) translateY(-8px) scale(1.04) rotateX(${rx}deg) rotateY(${ry}deg)`;
                    card.style.zIndex = '10';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.zIndex = '1';
                });
            });

               NAVBAR SCROLL SHRINK
            const navbar = document.querySelector('nav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 60) {
                    navbar.classList.add('shadow-md');
                } else {
                    navbar.classList.remove('shadow-md');
                }
            }, { passive: true });