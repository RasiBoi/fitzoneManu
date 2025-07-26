/**
 * FitZone Fitness Center
 * Black and White Theme Override JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Force hero section to use white background
    const heroSection = document.getElementById('hero-section');
    if (heroSection) {
        heroSection.style.background = '#ffffff';
        heroSection.style.backgroundColor = '#ffffff';
        heroSection.style.backgroundImage = 'none';
    }
    
    // Force hero overlay to use white background
    const heroOverlay = document.querySelector('.hero-overlay');
    if (heroOverlay) {
        heroOverlay.style.background = 'linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.85))';
    }
    
    // Force hero particles to have transparent background
    const heroParticles = document.getElementById('hero-particles');
    if (heroParticles) {
        heroParticles.style.background = 'transparent';
        
        // Remove any canvas elements that might be causing purple background
        setTimeout(function() {
            const canvas = heroParticles.querySelector('canvas');
            if (canvas) {
                canvas.style.background = 'transparent';
            }
        }, 500);
    }
    
    // Set text colors to black
    const heroTextArea = document.querySelector('.hero-text-area');
    if (heroTextArea) {
        heroTextArea.style.color = '#000000';
    }
    
    const heroSubtitle = document.querySelector('.hero-subtitle');
    if (heroSubtitle) {
        heroSubtitle.style.color = '#000000';
    }
    
    const heroDescription = document.querySelector('.hero-description');
    if (heroDescription) {
        heroDescription.style.color = '#000000';
    }
    
    // Apply grayscale filter to hero image
    const heroImage = document.querySelector('.hero-image');
    if (heroImage) {
        heroImage.style.filter = 'grayscale(100%)';
        heroImage.style.transition = 'none';
    }
    
    // Remove animations from hero stats
    const heroStats = document.querySelectorAll('.hero-stat');
    heroStats.forEach(stat => {
        stat.style.animation = 'none';
        stat.style.backgroundColor = '#ffffff';
        stat.style.border = '1px solid #ddd';
    });
    
    // Force About section to use black and white
    const aboutSection = document.querySelector('.about-section');
    if (aboutSection) {
        aboutSection.style.backgroundColor = '#ffffff';
        aboutSection.style.color = '#000000';
    }
    
    // Set about feature icons to black
    const aboutFeatureIcons = document.querySelectorAll('.about-feature-icon i');
    aboutFeatureIcons.forEach(icon => {
        icon.style.color = '#000000';
    });
    
    // Set about heading colors
    const aboutHeadings = document.querySelectorAll('.about-feature-content h4');
    aboutHeadings.forEach(heading => {
        heading.style.color = '#000000';
    });
    
    // Force Classes section to use black and white
    const classesSection = document.querySelector('.classes-section');
    if (classesSection) {
        classesSection.style.backgroundColor = '#ffffff';
        classesSection.style.color = '#000000';
    }
    
    // Update class cards to black and white
    const classCards = document.querySelectorAll('.class-card');
    classCards.forEach(card => {
        card.style.backgroundColor = '#ffffff';
        card.style.border = '1px solid #000000';
    });
    
    // Update class titles and content
    const classTitles = document.querySelectorAll('.class-title');
    classTitles.forEach(title => {
        title.style.color = '#000000';
    });
    
    const classDescriptions = document.querySelectorAll('.class-description');
    classDescriptions.forEach(desc => {
        desc.style.color = '#555555';
    });
    
    // Apply grayscale to class images
    const classImages = document.querySelectorAll('.class-image img');
    classImages.forEach(img => {
        img.style.filter = 'grayscale(100%)';
    });
    
    // Make all difficulty labels black with white borders
    const difficultyLabels = document.querySelectorAll('.class-difficulty');
    difficultyLabels.forEach(label => {
        label.style.backgroundColor = '#000000';
        label.style.color = '#ffffff';
        label.style.border = '1px solid white';
    });
    
    // Force Trainers section to use black and white
    const trainersSection = document.querySelector('.trainers-section');
    if (trainersSection) {
        trainersSection.style.backgroundColor = '#ffffff';
        trainersSection.style.color = '#000000';
    }
    
    // Ensure trainer cards use black and white styling
    const trainerCards = document.querySelectorAll('.trainer-card-inner');
    trainerCards.forEach(card => {
        card.style.backgroundColor = '#ffffff';
        card.style.border = '1px solid #000000';
    });
    
    // Apply grayscale filter to trainer images
    const trainerImages = document.querySelectorAll('.trainer-image');
    trainerImages.forEach(img => {
        img.style.filter = 'grayscale(100%)';
        img.style.border = '1px solid #000000';
    });
    
    // Ensure trainer text is black
    const trainerNames = document.querySelectorAll('.trainer-name');
    trainerNames.forEach(name => {
        name.style.color = '#000000';
    });
    
    // Force Membership section to use black and white
    const membershipSection = document.querySelector('.membership-section');
    if (membershipSection) {
        membershipSection.style.backgroundColor = '#ffffff';
        membershipSection.style.color = '#000000';
    }
    
    // Apply black and white styling to membership plans
    const membershipCards = document.querySelectorAll('.membership-card');
    membershipCards.forEach(card => {
        card.style.backgroundColor = '#ffffff';
        card.style.border = '1px solid #000000';
    });
    
    // Style the popular badge and card
    const popularBadge = document.querySelector('.popular-badge');
    if (popularBadge) {
        popularBadge.style.backgroundColor = '#000000';
    }
    
    // Style pricing
    const pricingAmounts = document.querySelectorAll('.amount');
    pricingAmounts.forEach(amount => {
        amount.style.color = '#000000';
    });
    
    // Style buttons
    const membershipButtons = document.querySelectorAll('.membership-card .btn');
    membershipButtons.forEach(btn => {
        btn.style.backgroundColor = '#000000';
        btn.style.borderColor = '#000000';
    });
    
    const heroStatNumber = document.querySelectorAll('.hero-stat-number');
    if (heroStatNumber) {
        heroStatNumber.forEach(el => {
            el.style.color = '#000000';
        });
    }
    
    const heroStatLabel = document.querySelectorAll('.hero-stat-label');
    if (heroStatLabel) {
        heroStatLabel.forEach(el => {
            el.style.color = '#000000';
        });
    }
    
    // Apply grayscale to all images in services section
    const serviceIcons = document.querySelectorAll('.service-icon i');
    serviceIcons.forEach(icon => {
        icon.style.color = '#000000';
    });
    
    // Apply grayscale to testimonial images
    const testimonialImages = document.querySelectorAll('.client-photo img');
    testimonialImages.forEach(img => {
        img.style.filter = 'grayscale(100%)';
    });
    
    // Style the testimonial section
    const testimonialCards = document.querySelectorAll('.testimonial-card');
    testimonialCards.forEach(card => {
        card.style.backgroundColor = '#ffffff';
        card.style.border = '1px solid #ddd';
    });
    
    // Style the CTA/Contact section
    const ctaSection = document.getElementById('cta-section');
    if (ctaSection) {
        ctaSection.style.backgroundColor = '#ffffff';
    }
    
    // Apply black and white to footer with improved visibility
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.classList.remove('bg-black', 'text-light');
        footer.classList.add('bg-light', 'text-dark');
        
        const footerHeadings = footer.querySelectorAll('h4, h5');
        footerHeadings.forEach(heading => {
            heading.style.color = '#000000';
            heading.style.fontWeight = '700';
        });
        
        // Improve text visibility in the footer
        const footerParagraphs = footer.querySelectorAll('p');
        footerParagraphs.forEach(p => {
            p.style.color = '#333333';
            p.style.fontWeight = '500';
        });
        
        const footerLinks = footer.querySelectorAll('a');
        footerLinks.forEach(link => {
            link.style.color = '#333333';
        });
        
        const footerIcons = footer.querySelectorAll('i');
        footerIcons.forEach(icon => {
            icon.style.color = '#000000';
        });
        
        const footerImages = footer.querySelectorAll('img');
        footerImages.forEach(img => {
            img.style.filter = 'grayscale(100%)';
        });
    }
    
    // Force navbar to be white with black text
    const navbar = document.getElementById('custom-navbar');
    if (navbar) {
        navbar.classList.remove('navbar-dark', 'bg-dark');
        navbar.classList.add('navbar-light', 'bg-light');
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
    }
    
    // Force ALL nav links to be black with extra specificity
    const navLinks = document.querySelectorAll('.nav-link, #navbarMain .nav-link, .navbar-nav .nav-link');
    navLinks.forEach(link => {
        link.style.color = '#000000';
        link.style.fontWeight = '600';
    });
    
    // Make search icon black
    const searchIcon = document.getElementById('search-toggle');
    if (searchIcon) {
        searchIcon.style.color = '#000000';
    }
    
    // Make sure all icons in navbar are black
    const navbarIcons = document.querySelectorAll('#custom-navbar i');
    navbarIcons.forEach(icon => {
        icon.style.color = '#000000';
    });
    
    // Ensure login button has correct colors
    const loginBtn = document.querySelector('.btn-login');
    if (loginBtn) {
        loginBtn.style.color = '#000000';
        loginBtn.style.borderColor = '#000000';
    }
});
