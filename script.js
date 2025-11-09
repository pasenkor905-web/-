document.addEventListener('DOMContentLoaded', function() {
    
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
            document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
        });
    }


    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('.header')?.offsetHeight || 70;
                const targetPosition = target.offsetTop - headerHeight - 10;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                

                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    });


    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, 5000);
    });


    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);


    initBanner();
    initCategories();
    initGallery();
    initReviews();
    initForms();
    initContacts();
    initFooter();
    initScrollAnimations();
    optimizeImagesForMobile();
});


document.addEventListener('click', function(event) {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navMenu && navToggle && !event.target.closest('.nav-container')) {
        navMenu.classList.remove('active');
        navToggle.classList.remove('active');
        document.body.style.overflow = '';
    }
});


document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const navMenu = document.getElementById('navMenu');
        const navToggle = document.getElementById('navToggle');
        
        if (navMenu && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            navToggle.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
});



function initBanner() {
    const bannerCard = document.querySelector('.banner-card');
    const bannerBtn = document.querySelector('.banner-btn');
    const bannerSection = document.querySelector('.banner-section');
    

    if (bannerCard) {
        setTimeout(() => {
            bannerCard.classList.add('fade-in-up');
        }, 300);
    }


    if (bannerBtn) {
        bannerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const registrationSection = document.getElementById('registration');
            if (registrationSection) {
                const headerHeight = document.querySelector('.header')?.offsetHeight || 70;
                const targetPosition = registrationSection.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    }


    function updateBannerCard() {
        const scrolled = window.scrollY;
        
        if (bannerCard && bannerSection) {
            const sectionRect = bannerSection.getBoundingClientRect();
            

            bannerCard.style.position = '';
            bannerCard.style.top = '';
            bannerCard.style.left = '';
            bannerCard.style.transform = '';
            bannerCard.style.zIndex = '';
            bannerCard.style.margin = '';
            bannerCard.style.width = '';
            bannerCard.style.maxWidth = '';
            

            const backgroundImage = document.querySelector('.background-image');
            if (backgroundImage) {
                const parallaxSpeed = 0.3;
                const yPos = -(scrolled * parallaxSpeed);
                backgroundImage.style.transform = `scale(1.1) translateY(${yPos}px)`;
            }
        
        
            const bannerOverlay = document.querySelector('.banner-overlay');
            if (bannerOverlay) {
                const scrollProgress = Math.max(0, Math.min(1, -sectionRect.top / (sectionRect.height - window.innerHeight)));
                const opacity = 1 - Math.min(1, scrollProgress * 1.5);
                bannerOverlay.style.opacity = opacity;
            }
        }
    }

    let ticking = false;
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateBannerCard();
                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    

    updateBannerCard();


    function updateBannerStyles() {
        const bannerCard = document.querySelector('.banner-card');
        if (!bannerCard) return;
        
        const viewportWidth = window.innerWidth;
        
        const wasFixed = bannerCard.style.position === 'fixed';
        bannerCard.style.position = '';
        bannerCard.style.top = '';
        bannerCard.style.left = '';
        bannerCard.style.transform = '';
        
        if (viewportWidth <= 480) {
            bannerCard.style.maxWidth = 'min(380px, 92vw)';
            bannerCard.style.padding = 'clamp(20px, 7vw, 30px) clamp(15px, 6vw, 25px)';
        } else if (viewportWidth <= 768) {
            bannerCard.style.maxWidth = 'min(450px, 90vw)';
            bannerCard.style.padding = 'clamp(25px, 6vw, 35px) clamp(20px, 5vw, 30px)';
        } else {
            bannerCard.style.maxWidth = 'min(550px, 85vw)';
            bannerCard.style.padding = '50px 40px';
        }

        if (wasFixed) {
            setTimeout(() => {
                bannerCard.style.position = 'fixed';
                bannerCard.style.top = '50%';
                bannerCard.style.left = '50%';
                bannerCard.style.transform = 'translate(-50%, -50%)';
            }, 10);
        }
    }

    window.addEventListener('resize', debounce(updateBannerStyles, 250));
    window.addEventListener('orientationchange', updateBannerStyles);
    
    updateBannerStyles();
}



function initCategories() {

    window.toggleDescription = function(id) {
        const description = document.getElementById(id);
        const isActive = description.classList.contains('active');
        

        document.querySelectorAll('.category-description.active').forEach(desc => {
            desc.classList.remove('active');
        });
        
        if (!isActive) {
            description.classList.add('active');
            
            setTimeout(() => {
                const closeOnClick = function(e) {
                    if (!description.contains(e.target) && !e.target.closest('.category-label')) {
                        description.classList.remove('active');
                        document.removeEventListener('click', closeOnClick);
                    }
                };
                document.addEventListener('click', closeOnClick);
            }, 10);
        }
    }

    const categoryCards = document.querySelectorAll('.category-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, entry.target.dataset.delay || 0);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    categoryCards.forEach((element, index) => {
        element.dataset.delay = index * 200;
        observer.observe(element);
    });

    const categoryLabels = document.querySelectorAll('.category-label');
    
    categoryLabels.forEach(label => {
        label.addEventListener('touchend', function(e) {
            e.preventDefault();
            const categoryCard = this.closest('.category-card');
            const description = categoryCard.querySelector('.category-description');
            const isActive = description.classList.contains('active');
            
            document.querySelectorAll('.category-description.active').forEach(desc => {
                desc.classList.remove('active');
            });
            
            if (!isActive) {
                description.classList.add('active');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeDescriptions = document.querySelectorAll('.category-description.active');
            activeDescriptions.forEach(desc => {
                desc.classList.remove('active');
            });
        }
    });
}


function initGallery() {
    const gallerySection = document.querySelector('#gallery .container');
    
    window.toggleGalleryOverlay = function(index) {
        const galleryItems = document.querySelectorAll('.gallery-item');
        const currentItem = galleryItems[index];
        const isActive = currentItem.classList.contains('active');
        
        galleryItems.forEach(item => {
            item.classList.remove('active');
        });
        
        if (!isActive) {
            currentItem.classList.add('active');
            
            const handleKeydown = function(e) {
                if (e.key === 'Escape') {
                    currentItem.classList.remove('active');
                    document.removeEventListener('keydown', handleKeydown);
                }
            };
            document.addEventListener('keydown', handleKeydown);
        }
    }

    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            toggleGalleryOverlay(index);
        });

        item.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleGalleryOverlay(index);
            }
        });

        item.setAttribute('tabindex', '0');
        
        let touchStartX = 0;
        let touchStartY = 0;
        
        item.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            this.classList.add('touch-active');
        });
        
        item.addEventListener('touchend', function(e) {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const diffX = Math.abs(touchEndX - touchStartX);
            const diffY = Math.abs(touchEndY - touchStartY);
            
            if (diffX < 10 && diffY < 10) {
                this.click();
            }
            
            this.classList.remove('touch-active');
        });
    });

    const galleryObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    const galleryItems = document.querySelectorAll('.gallery-item');
                    galleryItems.forEach((item, index) => {
                        setTimeout(() => {
                            item.classList.add('visible');
                        }, index * 100); 
                    });
                }, 200);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    if (gallerySection) {
        galleryObserver.observe(gallerySection);
    }

    document.addEventListener('click', function(event) {
        const isClickInsideGallery = event.target.closest('.gallery-item');
        if (!isClickInsideGallery) {
            document.querySelectorAll('.gallery-item.active').forEach(item => {
                item.classList.remove('active');
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.gallery-item.active').forEach(item => {
                item.classList.remove('active');
            });
        }
    });

    function optimizeGalleryImages() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        const isMobile = window.innerWidth <= 768;
        
        galleryItems.forEach(item => {
            const img = item.querySelector('.gallery-img');
            if (img && isMobile) {
                // Ensure images are properly displayed on mobile
                img.style.objectFit = 'cover';
                img.style.objectPosition = 'center top';
                img.style.backgroundColor = '#f8f9fa';
            } else if (img) {
                img.style.objectFit = 'cover';
                img.style.objectPosition = 'center top';
                img.style.backgroundColor = 'transparent';
            }
        });
    }
    
    window.addEventListener('resize', debounce(optimizeGalleryImages, 250));
    optimizeGalleryImages();
    
    document.addEventListener('keydown', function(e) {
        const activeItem = document.querySelector('.gallery-item.active');
        if (activeItem && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
            e.preventDefault();
            const allItems = Array.from(document.querySelectorAll('.gallery-item'));
            const currentIndex = allItems.indexOf(activeItem);
            let nextIndex;

            if (e.key === 'ArrowLeft') {
                nextIndex = currentIndex > 0 ? currentIndex - 1 : allItems.length - 1;
            } else {
                nextIndex = currentIndex < allItems.length - 1 ? currentIndex + 1 : 0;
            }

            toggleGalleryOverlay(nextIndex);
            
            allItems[nextIndex].focus();
        }
    });
}


function initReviews() {
    window.setRating = function(rating) {
        const stars = document.querySelectorAll('.rating-stars .fa-star');
        const ratingInput = document.getElementById('rating');
        
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
        
        if (ratingInput) {
            ratingInput.value = rating;
        }
        
        stars[rating - 1].style.transform = 'scale(1.3)';
        setTimeout(() => {
            stars[rating - 1].style.transform = 'scale(1.2)';
        }, 150);
    }

    const reviewCards = document.querySelectorAll('.review-card');
    const addReviewSection = document.querySelector('.add-review-section');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, entry.target.dataset.delay || 0);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    reviewCards.forEach((element, index) => {
        element.dataset.delay = index * 150;
        observer.observe(element);
    });

    if (addReviewSection) {
        observer.observe(addReviewSection);
    }

    const ratingStars = document.querySelectorAll('.rating-stars .fa-star');
    ratingStars.forEach((star, index) => {
        star.addEventListener('mouseenter', function() {
            setRating(index + 1);
        });
        
        star.addEventListener('touchstart', function() {
            setRating(index + 1);
        });
    });

    const textarea = document.getElementById('review_text');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        setTimeout(() => {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }, 100);
    }
}


function initForms() {
    const photoInput = document.getElementById('photo');
    const musicInput = document.getElementById('music');
    const photoInfo = document.getElementById('photoInfo');
    const musicInfo = document.getElementById('musicInfo');

    function updateFileInfo(input, infoElement, type) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileName = file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name;
            
            infoElement.textContent = `${type}: ${fileName} (${fileSize} MB)`;
            infoElement.style.color = 'var(--mint)';
            infoElement.style.fontWeight = '600';
            
            infoElement.style.transform = 'scale(1.05)';
            setTimeout(() => {
                infoElement.style.transform = 'scale(1)';
            }, 200);
        } else {
            infoElement.textContent = `Не выбран ни один файл`;
            infoElement.style.color = 'var(--text-light)';
            infoElement.style.fontWeight = 'normal';
        }
    }

    if (photoInput && photoInfo) {
        photoInput.addEventListener('change', function() {
            updateFileInfo(this, photoInfo, 'Фото');
        });
    }

    if (musicInput && musicInfo) {
        musicInput.addEventListener('change', function() {
            updateFileInfo(this, musicInfo, 'Аудио');
        });
    }

    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length === 0) {
                e.target.value = '';
                return;
            }

            if (value[0] === '7' || value[0] === '8') {
                value = value.substring(1);
            }

            let formattedValue = '+7 ';
            
            if (value.length > 0) {
                formattedValue += '(' + value.substring(0, 3);
            }
            if (value.length > 3) {
                formattedValue += ') ' + value.substring(3, 6);
            }
            if (value.length > 6) {
                formattedValue += '-' + value.substring(6, 8);
            }
            if (value.length > 8) {
                formattedValue += '-' + value.substring(8, 10);
            }

            e.target.value = formattedValue;
        });
        
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numbers = pastedText.replace(/\D/g, '');
            this.value = numbers;
            this.dispatchEvent(new Event('input'));
        });
    }

    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const formElements = form.querySelectorAll('.form-group, .submit-btn');
        if (formElements.length > 0) {
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 200 + index * 100);
            });
        }

        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                field.classList.remove('error');
                
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    showFieldError(field, 'Это поле обязательно для заполнения');
                } else {
                    // Specific validations
                    if (field.type === 'email' && !isValidEmail(field.value)) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Введите корректный email адрес');
                    }
                    
                    if (field.id === 'phone' && !isValidPhone(field.value)) {
                        isValid = false;
                        field.classList.add('error');
                        showFieldError(field, 'Введите корректный номер телефона');
                    }
                    
                    if (field.id === 'age') {
                        const age = parseInt(field.value);
                        if (age < 16 || age > 99) {
                            isValid = false;
                            field.classList.add('error');
                            showFieldError(field, 'Возраст должен быть от 16 до 99 лет');
                        }
                    }
                }
            });

            if (form.classList.contains('registration-form')) {
                const checkboxes = form.querySelectorAll('input[name="categories[]"]');
                const checked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                
                if (!checked) {
                    isValid = false;
                    const checkboxGroup = form.querySelector('.checkbox-group');
                    if (checkboxGroup) {
                        checkboxGroup.classList.add('error');
                        showCheckboxError(checkboxGroup, 'Выберите хотя бы одну категорию');
                    }
                } else {
                    const checkboxGroup = form.querySelector('.checkbox-group');
                    if (checkboxGroup) {
                        checkboxGroup.classList.remove('error');
                        hideCheckboxError(checkboxGroup);
                    }
                }
            }

            if (!isValid) {
                e.preventDefault();
                
                const firstError = form.querySelector('.error');
                if (firstError) {
                    const headerHeight = document.querySelector('.header')?.offsetHeight || 70;
                    const errorPosition = firstError.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                    
                    window.scrollTo({
                        top: errorPosition,
                        behavior: 'smooth'
                    });
                    
                    if (firstError.tagName === 'INPUT' || firstError.tagName === 'TEXTAREA') {
                        firstError.focus();
                    }
                }
            } else {
                const submitBtn = form.querySelector('.submit-btn');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
                    submitBtn.disabled = true;
                }
            }
        });
        
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function isValidPhone(phone) {
        const phoneRegex = /^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/;
        return phoneRegex.test(phone);
    }
    
    function validateField(field) {
        field.classList.remove('error');
        hideFieldError(field);
        
        if (!field.value.trim() && field.hasAttribute('required')) {
            field.classList.add('error');
            showFieldError(field, 'Это поле обязательно для заполнения');
            return false;
        }
        
        if (field.type === 'email' && !isValidEmail(field.value)) {
            field.classList.add('error');
            showFieldError(field, 'Введите корректный email адрес');
            return false;
        }
        
        if (field.id === 'phone' && !isValidPhone(field.value)) {
            field.classList.add('error');
            showFieldError(field, 'Введите корректный номер телефона');
            return false;
        }
        
        if (field.id === 'age') {
            const age = parseInt(field.value);
            if ((age < 16 || age > 99) && field.value.trim() !== '') {
                field.classList.add('error');
                showFieldError(field, 'Возраст должен быть от 16 до 99 лет');
                return false;
            }
        }
        
        return true;
    }
    
    function showFieldError(field, message) {
        hideFieldError(field);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.cssText = `
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
            animation: fadeInUp 0.3s ease;
        `;
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    function hideFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    function showCheckboxError(checkboxGroup, message) {
        hideCheckboxError(checkboxGroup);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'checkbox-error';
        errorDiv.style.cssText = `
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 10px;
            display: block;
            animation: fadeInUp 0.3s ease;
        `;
        errorDiv.textContent = message;
        
        checkboxGroup.parentNode.appendChild(errorDiv);
    }
    
    function hideCheckboxError(checkboxGroup) {
        const existingError = checkboxGroup.parentNode.querySelector('.checkbox-error');
        if (existingError) {
            existingError.remove();
        }
    }
}


function initContacts() {
    const contactCards = document.querySelectorAll('.contact-card');
    const contactForm = document.querySelector('.contact-form');
    const mapContainer = document.querySelector('.map-container');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, entry.target.dataset.delay || 0);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    contactCards.forEach((element, index) => {
        element.dataset.delay = index * 100;
        observer.observe(element);
    });

    if (contactForm) {
        observer.observe(contactForm);
    }

    if (mapContainer) {
        observer.observe(mapContainer);
    }

    const contactTextarea = document.getElementById('contact_message');
    if (contactTextarea) {
        contactTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        setTimeout(() => {
            contactTextarea.style.height = 'auto';
            contactTextarea.style.height = (contactTextarea.scrollHeight) + 'px';
        }, 100);
    }

    if (typeof ymaps !== 'undefined') {
        ymaps.ready(initMap);
    } else {
        loadYandexMap();
    }

    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        try {
            const myMap = new ymaps.Map("map", {
                center: [53.347222, 83.778611],
                zoom: 16,
                controls: ['zoomControl', 'fullscreenControl']
            });
            
            const myPlacemark = new ymaps.Placemark([53.347222, 83.778611], {
                hintContent: 'Конкурс Прожектор',
                balloonContent: 'г. Барнаул, пр. Ленина, 61<br>Телефон: +7 (3852) 12-34-56'
            }, {
                preset: 'islands#greenIcon',
                iconColor: '#43d8a4'
            });
            
            myMap.geoObjects.add(myPlacemark);
            
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    myPlacemark.balloon.open();
                }, 1000);
            }
        } catch (error) {
            console.error('Error initializing Yandex Map:', error);
            const mapElement = document.getElementById('map');
            if (mapElement) {
                mapElement.innerHTML = `
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f8f9fa;color:#666;flex-direction:column;gap:10px;">
                        <i class="fas fa-map-marker-alt" style="font-size:2rem;"></i>
                        <p>г. Барнаул, пр. Ленина, 61</p>
                        <small>Карта временно недоступна</small>
                    </div>
                `;
            }
        }
    }

    function loadYandexMap() {
        const script = document.createElement('script');
        script.src = 'https://api-maps.yandex.ru/2.1/?apikey=your_api_key&lang=ru_RU';
        script.onload = function() {
            ymaps.ready(initMap);
        };
        script.onerror = function() {
            console.error('Failed to load Yandex Maps API');
            // Show fallback
            const mapElement = document.getElementById('map');
            if (mapElement) {
                mapElement.innerHTML = `
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f8f9fa;color:#666;flex-direction:column;gap:10px;">
                        <i class="fas fa-map-marker-alt" style="font-size:2rem;"></i>
                        <p>г. Барнаул, пр. Ленина, 61</p>
                        <small>Карта временно недоступна</small>
                    </div>
                `;
            }
        };
        document.head.appendChild(script);
    }
}


function initFooter() {
    const footerSections = document.querySelectorAll('.footer-section');
    const footerBottom = document.querySelector('.footer-bottom');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, entry.target.dataset.delay || 0);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    footerSections.forEach((element, index) => {
        element.dataset.delay = index * 100;
        observer.observe(element);
    });

    if (footerBottom) {
        footerBottom.dataset.delay = 400;
        observer.observe(footerBottom);
    }

    document.querySelectorAll('.footer a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('.header')?.offsetHeight || 70;
                const targetPosition = target.offsetTop - headerHeight - 10;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    const copyrightElement = document.querySelector('.footer-bottom p');
    if (copyrightElement) {
        const currentYear = new Date().getFullYear();
        copyrightElement.innerHTML = copyrightElement.innerHTML.replace('2024', currentYear);
    }
}


function initScrollAnimations() {
    const sections = document.querySelectorAll('.section');
    
    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('section-visible');
                
                if (entry.target.id !== 'gallery') {
                    const animatedElements = entry.target.querySelectorAll('.fade-in-up, .benefit-card, .category-card, .review-card, .contact-card');
                    animatedElements.forEach((element, index) => {
                        setTimeout(() => {
                            element.classList.add('visible');
                        }, index * 150);
                    });
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    sections.forEach(section => {
        sectionObserver.observe(section);
    });

    const animatedElements = document.querySelectorAll('.fade-in-up, .benefit-card, .review-card, .contact-card');
    
    const elementObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, entry.target.dataset.delay || 0);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -20px 0px'
    });

    animatedElements.forEach((element, index) => {
        element.dataset.delay = index * 100;
        elementObserver.observe(element);
    });

    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 70px;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(135deg, var(--mint) 0%, var(--pink) 100%);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', function() {
        const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (window.pageYOffset / windowHeight) * 100;
        progressBar.style.width = scrolled + '%';
    });
}


function optimizeImagesForMobile() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    const categoryCards = document.querySelectorAll('.category-card');
    const isMobile = window.innerWidth <= 768;
    
    galleryItems.forEach(item => {
        const img = item.querySelector('.gallery-img');
        if (img && isMobile) {
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center top';
        } else if (img) {
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center top';
        }
    });
    
    categoryCards.forEach(card => {
        const img = card.querySelector('.category-image');
        if (img && isMobile) {
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center';
        } else if (img) {
            img.style.objectFit = 'cover';
            img.style.objectPosition = 'center';
        }
    });
}


function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        const submitBtns = document.querySelectorAll('.submit-btn:disabled');
        submitBtns.forEach(btn => {
            if (btn.innerHTML.includes('fa-spinner')) {
                btn.innerHTML = 'Отправить';
                btn.disabled = false;
            }
        });
    }
});

window.addEventListener('load', function() {
    const submitBtns = document.querySelectorAll('.submit-btn:disabled');
    submitBtns.forEach(btn => {
        if (btn.innerHTML.includes('fa-spinner')) {
            btn.innerHTML = btn.innerHTML.replace('<i class="fas fa-spinner fa-spin"></i> ', '');
            btn.disabled = false;
        }
    });
    
    const images = document.querySelectorAll('img[data-src]');
    images.forEach(img => {
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
    });

    optimizeImagesForMobile();
});

window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});

if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            const perfData = performance.timing;
            const loadTime = perfData.loadEventEnd - perfData.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
        }, 0);
    });
}

window.addEventListener('resize', debounce(optimizeImagesForMobile, 250));
window.addEventListener('orientationchange', optimizeImagesForMobile);

console.log('✅ Конкурс Прожектор - Enhanced JavaScript loaded successfully!');