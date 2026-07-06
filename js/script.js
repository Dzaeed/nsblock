(function() {
    'use strict';

    function applyTheme(theme) {
        var html = document.documentElement;
        var body = document.body;
        var themeToggle = document.querySelector('.theme-toggle');
        var icon = themeToggle ? themeToggle.querySelector('i') : null;
        var isDark = theme === 'dark';

        html.setAttribute('data-theme', isDark ? 'dark' : 'light');
        if (body) {
            body.classList.toggle('dark-mode', isDark);
            body.classList.toggle('light-mode', !isDark);
        }

        if (themeToggle) {
            themeToggle.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
            themeToggle.setAttribute('title', isDark ? 'Ubah ke mode terang' : 'Ubah ke mode gelap');
        }

        if (icon) {
            icon.classList.remove('fa-moon', 'fa-sun');
            icon.classList.add(isDark ? 'fa-sun' : 'fa-moon');
        }
    }

    function initThemeToggle() {
        var themeToggle = document.querySelector('.theme-toggle');
        if (!themeToggle) return;

        var savedTheme = null;
        try {
            savedTheme = localStorage.getItem('theme');
        } catch (err) {
            savedTheme = null;
        }
        var systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

        applyTheme(initialTheme);

        themeToggle.addEventListener('click', function() {
            var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            var nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
            try {
                localStorage.setItem('theme', nextTheme);
            } catch (err) {
                /* Ignore storage errors in restricted environments */
            }
        });
    }

    function initAccountMenu() {
        var accountMenu = document.querySelector('.account-menu');
        if (!accountMenu) return;

        var toggle = accountMenu.querySelector('.account-menu-toggle');
        var dropdown = accountMenu.querySelector('.account-menu-dropdown');
        if (!toggle || !dropdown) return;

        function closeMenu() {
            accountMenu.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', function(event) {
            event.stopPropagation();
            var isOpen = accountMenu.classList.toggle('active');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        dropdown.addEventListener('click', function(event) {
            if (event.target.closest('.theme-toggle')) return;
            closeMenu();
        });

        document.addEventListener('click', function(event) {
            if (!accountMenu.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    }
    
    function initProductDetailModal() {
        var modalOverlay = document.querySelector('#productModalOverlay');
        if (!modalOverlay) return;

        var productButtons = document.querySelectorAll('.product-detail-button');
        var modalImage = document.querySelector('#productModalImage');
        var modalName = document.querySelector('#productModalName');
        var modalCategory = document.querySelector('#productModalCategory');
        var modalSize = document.querySelector('#productModalSize');
        var modalPrice = document.querySelector('#productModalPrice');
        var modalStock = document.querySelector('#productModalStock');
        var modalDescription = document.querySelector('#productModalDescription');
        var modalCartButton = document.querySelector('#productModalCartButton');
        var closeModalButtons = modalOverlay.querySelectorAll('.product-modal-close, .modal-close-btn');

        function closeModal() {
            modalOverlay.classList.remove('active');
            modalOverlay.setAttribute('aria-hidden', 'true');
        }

        function openModal(button) {
            modalImage.src = button.dataset.productImage || '';
            modalImage.alt = button.dataset.productName || 'Detail Produk';
            modalName.textContent = button.dataset.productName || '';
            modalCategory.textContent = button.dataset.productCategory || '';
            if (modalSize) {
                modalSize.textContent = button.dataset.productUkuran || 'Ukuran belum tersedia';
            }
            if (modalPrice) {
                modalPrice.textContent = 'Harga: ' + formatCurrency(button.dataset.productPrice || 0);
            }
            if (modalStock) {
                modalStock.textContent = 'Stok: ' + (button.dataset.productStock || 0);
            }
            if (modalCartButton) {
                modalCartButton.dataset.productId = button.dataset.productId || '';
                modalCartButton.dataset.productName = button.dataset.productName || '';
                modalCartButton.dataset.productCategory = button.dataset.productCategory || '';
                modalCartButton.dataset.productUkuran = button.dataset.productUkuran || '';
                modalCartButton.dataset.productPrice = button.dataset.productPrice || '0';
                modalCartButton.dataset.productStock = button.dataset.productStock || '0';
                modalCartButton.dataset.productPavingRate = button.dataset.productPavingRate || '';
                modalCartButton.dataset.productImage = button.dataset.productImage || '';
                modalCartButton.dataset.requiresCalculator = button.dataset.requiresCalculator || '0';
            }
            modalDescription.textContent = button.dataset.productDescription || '';
            modalOverlay.classList.add('active');
            modalOverlay.setAttribute('aria-hidden', 'false');
        }

        function formatCurrency(value) {
            var number = Number(value || 0);
            return 'Rp ' + number.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        productButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                openModal(button);
            });
        });

        closeModalButtons.forEach(function(button) {
            button.addEventListener('click', closeModal);
        });

        modalOverlay.addEventListener('click', function(event) {
            if (event.target === modalOverlay) {
                closeModal();
            }
        });

        document.addEventListener('keyup', function(event) {
            if (event.key === 'Escape' && modalOverlay.classList.contains('active')) {
                closeModal();
            }
        });
    }

    function initProductCarousels() {
        var carousels = document.querySelectorAll('.category-section');
        if (!carousels.length) return;

        function updateButtons(section) {
            var track = section.querySelector('.product-grid');
            var prevButton = section.querySelector('[data-carousel-direction="prev"]');
            var nextButton = section.querySelector('[data-carousel-direction="next"]');
            var progress = section.querySelector('.product-carousel-status span');
            if (!track) return;

            var maxScroll = track.scrollWidth - track.clientWidth;
            if (prevButton && nextButton) {
                prevButton.disabled = track.scrollLeft <= 2;
                nextButton.disabled = track.scrollLeft >= maxScroll - 2;
            }

            if (progress) {
                var visibleRatio = track.scrollWidth > 0 ? track.clientWidth / track.scrollWidth : 1;
                var progressWidth = Math.min(100, Math.max(18, visibleRatio * 100));
                var scrollRatio = maxScroll > 0 ? track.scrollLeft / maxScroll : 0;
                var travel = 100 - progressWidth;
                progress.style.width = progressWidth + '%';
                progress.style.marginLeft = (scrollRatio * travel) + '%';
            }
        }

        carousels.forEach(function(section) {
            var track = section.querySelector('.product-grid');
            var buttons = section.querySelectorAll('.product-carousel-button');
            var isDragging = false;
            var didDrag = false;
            var startX = 0;
            var startScrollLeft = 0;
            if (!track) return;

            buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var direction = button.dataset.carouselDirection === 'prev' ? -1 : 1;
                    var card = track.querySelector('.product-card');
                    var gap = parseFloat(window.getComputedStyle(track).columnGap || '0') || 0;
                    var scrollAmount = card ? card.offsetWidth + gap : Math.max(track.clientWidth * 0.85, 260);

                    track.scrollBy({
                        left: direction * scrollAmount,
                        behavior: 'smooth'
                    });
                });
            });

            track.addEventListener('keydown', function(event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
                event.preventDefault();
                var direction = event.key === 'ArrowLeft' ? -1 : 1;
                var card = track.querySelector('.product-card');
                var gap = parseFloat(window.getComputedStyle(track).columnGap || '0') || 0;
                var scrollAmount = card ? card.offsetWidth + gap : Math.max(track.clientWidth * 0.85, 260);
                track.scrollBy({
                    left: direction * scrollAmount,
                    behavior: 'smooth'
                });
            });

            track.addEventListener('pointerdown', function(event) {
                if (event.button !== undefined && event.button !== 0) return;
                if (event.target.closest('button, a, input, select, textarea, label')) return;
                isDragging = true;
                didDrag = false;
                startX = event.clientX;
                startScrollLeft = track.scrollLeft;
                track.classList.add('dragging');
                track.setPointerCapture(event.pointerId);
            });

            track.addEventListener('pointermove', function(event) {
                if (!isDragging) return;
                var distance = event.clientX - startX;
                if (Math.abs(distance) > 6) {
                    didDrag = true;
                }
                track.scrollLeft = startScrollLeft - distance;
            });

            function stopDragging(event) {
                if (!isDragging) return;
                isDragging = false;
                track.classList.remove('dragging');
                if (event && track.hasPointerCapture(event.pointerId)) {
                    track.releasePointerCapture(event.pointerId);
                }
                window.setTimeout(function() {
                    didDrag = false;
                }, 80);
            }

            track.addEventListener('pointerup', stopDragging);
            track.addEventListener('pointercancel', stopDragging);
            track.addEventListener('pointerleave', stopDragging);

            track.addEventListener('click', function(event) {
                if (didDrag) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);

            track.addEventListener('scroll', function() {
                updateButtons(section);
            }, { passive: true });

            updateButtons(section);
        });

        window.addEventListener('resize', function() {
            carousels.forEach(updateButtons);
        });
    }
    
    function init() {
        initThemeToggle();
        initAccountMenu();
        // initProductDetailModal(); // Disabled: Using full-page product detail instead of modal
        initProductCarousels();

        var menuToggle = document.querySelector('.menu-toggle');
        var navbar = document.querySelector('.navbar');
        var dropdowns = document.querySelectorAll('.navbar .dropdown');
        
        if (menuToggle && navbar) {
            menuToggle.onclick = function() {
                menuToggle.classList.toggle('active');
                navbar.classList.toggle('active');
            };

            for (var d = 0; d < dropdowns.length; d++) {
                var dropButton = dropdowns[d].querySelector('.drop-btn');
                if (dropButton) {
                    dropButton.addEventListener('click', function(event) {
                        if (window.innerWidth <= 767) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            var parentDropdown = this.closest('.dropdown');
                            for (var x = 0; x < dropdowns.length; x++) {
                                if (dropdowns[x] !== parentDropdown) {
                                    dropdowns[x].classList.remove('active');
                                }
                            }
                            if (parentDropdown) {
                                parentDropdown.classList.toggle('active');
                            }
                        }
                    }, true);
                }
            }
            
            var navLinks = navbar.querySelectorAll('a');
            for (var i = 0; i < navLinks.length; i++) {
                navLinks[i].onclick = function(event) {
                    if (window.innerWidth <= 767 && this.classList.contains('drop-btn')) {
                        event.preventDefault();
                        return false;
                    }
                    menuToggle.classList.remove('active');
                    navbar.classList.remove('active');
                    for (var y = 0; y < dropdowns.length; y++) {
                        dropdowns[y].classList.remove('active');
                    }
                };
            }
            
            document.onclick = function(e) {
                if (!navbar.contains(e.target) && !menuToggle.contains(e.target)) {
                    if (navbar.classList.contains('active')) {
                        menuToggle.classList.remove('active');
                        navbar.classList.remove('active');
                        for (var z = 0; z < dropdowns.length; z++) {
                            dropdowns[z].classList.remove('active');
                        }
                    }
                }
            };
        }
        
        var header = document.querySelector('header');
        if (header) {
            window.onscroll = function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            };
        }
        
        var anchorLinks = document.querySelectorAll('a[href^="#"]');
        for (var j = 0; j < anchorLinks.length; j++) {
            anchorLinks[j].onclick = function(e) {
                var href = this.getAttribute('href');
                if (href && href !== '#' && href !== '') {
                    var target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        var headerHeight = header ? header.offsetHeight : 0;
                        var targetPos = target.offsetTop - headerHeight;
                        
                        window.scrollTo({
                            top: targetPos,
                            behavior: 'smooth'
                        });
                        
                        if (menuToggle && navbar) {
                            menuToggle.classList.remove('active');
                            navbar.classList.remove('active');
                            for (var q = 0; q < dropdowns.length; q++) {
                                dropdowns[q].classList.remove('active');
                            }
                        }
                    }
                }
            };
        }
        
        var dropdownLinks = document.querySelectorAll('.dropdown-content a[href^="#"]');
        for (var p = 0; p < dropdownLinks.length; p++) {
            dropdownLinks[p].onclick = function(e) {
                var href = this.getAttribute('href');
                if (href && href !== '#' && href !== '') {
                    e.preventDefault();
                    var target = document.querySelector(href);
                    if (target) {
                        var headerHeight = header ? header.offsetHeight : 0;
                        var targetPos = target.offsetTop - headerHeight - 20;
                        
                        window.scrollTo({
                            top: targetPos,
                            behavior: 'smooth'
                        });
                        
                        if (menuToggle && navbar) {
                            menuToggle.classList.remove('active');
                            navbar.classList.remove('active');
                            for (var r = 0; r < dropdowns.length; r++) {
                                dropdowns[r].classList.remove('active');
                            }
                        }
                    } else {
                        var productsSection = document.querySelector('#products');
                        if (productsSection) {
                            var headerHeight = header ? header.offsetHeight : 0;
                            var targetPos = productsSection.offsetTop - headerHeight;
                            window.scrollTo({
                                top: targetPos,
                                behavior: 'smooth'
                            });
                        }
                    }
                }
            };
        }
        
        var sections = document.querySelectorAll('section');
        var homeSection = document.querySelector('#home');
        if (homeSection) {
            homeSection.classList.add('visible');
        }
        
        if (window.IntersectionObserver) {
            var observer = new IntersectionObserver(function(entries) {
                for (var k = 0; k < entries.length; k++) {
                    if (entries[k].isIntersecting) {
                        entries[k].target.classList.add('visible');
                    }
                }
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            for (var l = 0; l < sections.length; l++) {
                if (sections[l].id !== 'home') {
                    observer.observe(sections[l]);
                }
            }
        } else {
            for (var m = 0; m < sections.length; m++) {
                sections[m].classList.add('visible');
            }
        }
        
        var navLinksActive = document.querySelectorAll('.navbar a[href^="#"]');
        
        function updateActiveNav() {
            var scrollPos = window.scrollY + (header ? header.offsetHeight + 100 : 100);
            for (var n = 0; n < sections.length; n++) {
                var section = sections[n];
                var sectionTop = section.offsetTop;
                var sectionHeight = section.offsetHeight;
                var sectionId = section.getAttribute('id');
                
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    for (var o = 0; o < navLinksActive.length; o++) {
                        navLinksActive[o].classList.remove('active');
                        if (navLinksActive[o].getAttribute('href') === '#' + sectionId) {
                            navLinksActive[o].classList.add('active');
                        }
                    }
                }
            }
        }
        
        window.onscroll = function() {
            if (header) {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
            updateActiveNav();
        };
        
        updateActiveNav();
        
        var resizeTimer;
        window.onresize = function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 767) {
                    if (menuToggle) menuToggle.classList.remove('active');
                    if (navbar) navbar.classList.remove('active');
                    for (var s = 0; s < dropdowns.length; s++) {
                        dropdowns[s].classList.remove('active');
                    }
                }
            }, 250);
        };
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
