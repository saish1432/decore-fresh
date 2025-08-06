// Global Variables
let cart = [];
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;

// Initialize website
document.addEventListener('DOMContentLoaded', function() {
    initializeBanner();
    initializeImageFlip();
    initializeImageZoom();
    initializeAnimations();
});

// Banner Slider Functionality
function initializeBanner() {
    if (totalSlides === 0) return;
    
    // Auto slide functionality
    setInterval(() => {
        nextSlide();
    }, 5000);
    
    // Navigation buttons
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
}

function nextSlide() {
    slides[currentSlide].classList.remove('active');
    currentSlide = (currentSlide + 1) % totalSlides;
    slides[currentSlide].classList.add('active');
}

function prevSlide() {
    slides[currentSlide].classList.remove('active');
    currentSlide = currentSlide === 0 ? totalSlides - 1 : currentSlide - 1;
    slides[currentSlide].classList.add('active');
}

// Image Flip Effect for Accessories
function initializeImageFlip() {
    const accessoryCards = document.querySelectorAll('.accessory-card');
    
    accessoryCards.forEach(card => {
        const images = card.querySelectorAll('.image-flip');
        if (images.length > 1) {
            let currentImage = 0;
            
            setInterval(() => {
                images[currentImage].classList.remove('active');
                currentImage = (currentImage + 1) % images.length;
                images[currentImage].classList.add('active');
            }, 3000);
        }
    });
}

// Image Zoom Modal
function initializeImageZoom() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const closeModal = document.querySelector('.close-modal');
    
    // Add click events to zoomable images
    document.querySelectorAll('.zoomable').forEach(img => {
        img.addEventListener('click', function() {
            modal.style.display = 'block';
            modalImg.src = this.src;
        });
    });
    
    // Add click events to zoom buttons
    document.querySelectorAll('.zoom-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const img = this.closest('.product-image').querySelector('img');
            modal.style.display = 'block';
            modalImg.src = img.src;
        });
    });
    
    // Close modal
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
}

// Scroll Animations
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);
    
    // Observe animated elements
    document.querySelectorAll('.animated-title, .animated-category, .about-point').forEach(el => {
        observer.observe(el);
    });
}

// Cart Functionality
function addToCart(productId, productName, price) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: price,
            quantity: 1
        });
    }
    
    updateCartDisplay();
    showNotification(`${productName} added to cart!`);
}

function addToCartAndShow(productId, productName, price) {
    addToCart(productId, productName, price);
    showCart();
}

function removeFromCart(productId) {
    const itemIndex = cart.findIndex(item => item.id === productId);
    if (itemIndex > -1) {
        cart.splice(itemIndex, 1);
        updateCartDisplay();
    }
}

function updateCartQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            updateCartDisplay();
        }
    }
}

function updateCartDisplay() {
    const cartSummary = document.getElementById('cartSummary');
    const cartItems = document.getElementById('cartItems');
    const totalQty = document.getElementById('totalQty');
    const totalValue = document.getElementById('totalValue');
    const cartIcon = document.getElementById('cartIcon');
    const cartCount = document.getElementById('cartCount');
    
    if (cart.length === 0) {
        if (cartSummary) {
            cartSummary.style.display = 'none';
        }
        if (cartIcon) {
            cartIcon.style.display = 'none';
        }
        return;
    }
    
    let totalQuantity = 0;
    let totalPrice = 0;
    let cartHTML = '';
    
    cart.forEach(item => {
        totalQuantity += item.quantity;
        totalPrice += item.price * item.quantity;
        
        cartHTML += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>₹${item.price} x ${item.quantity}</p>
                </div>
                <div class="cart-item-controls">
                    <button onclick="updateCartQuantity(${item.id}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateCartQuantity(${item.id}, 1)">+</button>
                    <button onclick="removeFromCart(${item.id})" class="remove-btn">×</button>
                </div>
            </div>
        `;
    });
    
    if (cartItems) cartItems.innerHTML = cartHTML;
    if (totalQty) totalQty.textContent = totalQuantity;
    if (totalValue) totalValue.textContent = totalPrice;
    
    // Update cart icon
    if (cartIcon) {
        cartIcon.style.display = 'flex';
    }
    if (cartCount) {
        cartCount.textContent = totalQuantity;
    }
}

function showCart() {
    const cartSummary = document.getElementById('cartSummary');
    if (cartSummary && cart.length > 0) {
        cartSummary.style.display = 'flex';
        updateCartDisplay();
    }
}

function closeCart() {
    const cartSummary = document.getElementById('cartSummary');
    if (cartSummary) {
        cartSummary.style.display = 'none';
    }
}

// WhatsApp Integration
function inquireProduct(productName) {
    const message = `Hi, I'm interested in ${productName}. Can you provide more details?`;
    const whatsappUrl = `https://wa.me/1234567890?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

function buyNow(productId, productName, price) {
    const message = `🛒 Product Order Details:\n\n` +
                   `📦 Product: ${productName}\n` +
                   `💰 Price: ₹${price}\n` +
                   `📊 Quantity: 1\n` +
                   `💳 Total Value: ₹${price}\n\n` +
                   `Thank you for purchasing! We will call you within 20 minutes to complete your payment online.`;
    
    const whatsappUrl = `https://wa.me/1234567890?text=${encodeURIComponent(message)}`;
    
    // Record the order
    recordOrder([{name: productName, quantity: 1, price: price}], price);
    
    window.open(whatsappUrl, '_blank');
}

function confirmPurchase() {
    if (cart.length === 0) {
        showNotification('Your cart is empty!');
        return;
    }
    
    let message = `🛒 Cart Order Details:\n\n`;
    let totalValue = 0;
    
    cart.forEach(item => {
        message += `📦 ${item.name}\n💰 ₹${item.price} x ${item.quantity} = ₹${item.price * item.quantity}\n\n`;
        totalValue += item.price * item.quantity;
    });
    
    message += `💳 Total Value: ₹${totalValue}\n\n`;
    message += `Thank you for purchasing! We will call you within 20 minutes to complete your payment online.`;
    
    const whatsappUrl = `https://wa.me/1234567890?text=${encodeURIComponent(message)}`;
    
    // Record the order
    recordOrder(cart, totalValue);
    
    // Clear cart
    cart = [];
    closeCart();
    
    window.open(whatsappUrl, '_blank');
}

// Record Order in Database
function recordOrder(items, totalValue) {
    const orderData = {
        items: items,
        total_value: totalValue,
        order_date: new Date().toISOString()
    };
    
    fetch('api/record_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Order recorded successfully');
        }
    })
    .catch(error => {
        console.error('Error recording order:', error);
    });
}

// Video Player Functions
function loadVideo(element, embedUrl) {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    player.src = embedUrl;
    modal.style.display = 'flex';
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    player.src = '';
    modal.style.display = 'none';
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

// Notification System
function showNotification(message) {
    // Create notification element if it doesn't exist
    let notification = document.querySelector('.notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification';
        document.body.appendChild(notification);
    }
    
    notification.textContent = message;
    notification.style.display = 'block';
    notification.style.opacity = '1';
    
    // Hide after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 300);
    }, 3000);
}

// Add notification styles
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        font-weight: bold;
    }
    
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .cart-item-info h4 {
        margin: 0 0 5px 0;
        font-size: 1rem;
    }
    
    .cart-item-info p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }
    
    .cart-item-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cart-item-controls button {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .cart-item-controls button:hover {
        background: #f8f9fa;
    }
    
    .remove-btn {
        background: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
    }
    
    .remove-btn:hover {
        background: #c82333 !important;
    }
`;
document.head.appendChild(notificationStyle);

// Smooth Scroll for Internal Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Performance Optimization - Lazy Loading Images
function lazyLoadImages() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Initialize lazy loading when DOM is ready
document.addEventListener('DOMContentLoaded', lazyLoadImages);