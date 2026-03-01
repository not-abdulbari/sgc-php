document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const resultContainer = document.getElementById('resultContainer');
    const loadingElement = document.getElementById('loading');
    const teamImage = document.getElementById('teamImage');
    const submitBtn = document.getElementById('submitBtn');
    
    // Disable form resubmission on refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        loadingElement.style.display = 'block';
        resultContainer.style.display = 'none';
        
        // Get form data
        const formData = new FormData(form);
        
        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            // Hide loading
            loadingElement.style.display = 'none';
            
            // Display result
            if (result.success) {
                document.getElementById('message').textContent = result.message;
                document.getElementById('teamName').textContent = result.team;
                document.getElementById('studentName').textContent = result.name;
                document.getElementById('studentRollNo').textContent = result.roll_no;
                
                // Update image source
                teamImage.src = `teams/${result.team.toLowerCase()}.webp`;
                teamImage.alt = `${result.team} Team`;
                
                // Show success styling
                resultContainer.className = 'result-container success';
                resultContainer.style.display = 'block';
                
                // Trigger confetti for new registration
                if (result.type === 'new') {
                    showConfetti();
                    
                    // Reset form after successful registration
                    form.reset();
                }
                
                // Fade in team image
                setTimeout(() => {
                    teamImage.classList.add('show');
                }, 100);
            } else {
                document.getElementById('message').textContent = result.message;
                document.getElementById('studentName').textContent = result.name;
                document.getElementById('studentRollNo').textContent = result.roll_no;
                resultContainer.className = 'result-container error';
                resultContainer.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            loadingElement.style.display = 'none';
            document.getElementById('message').textContent = 'An error occurred. Please try again.';
            resultContainer.className = 'result-container error';
            resultContainer.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
        }
    });
    
    // Function to create confetti effect
    function showConfetti() {
        const confettiContainer = document.createElement('div');
        confettiContainer.className = 'confetti-container';
        confettiContainer.style.display = 'block';
        
        document.body.appendChild(confettiContainer);
        
        const colors = ['#ff4d4d', '#ff9999', '#ffcccc', '#ffffff', '#ff6666'];
        const confettiCount = 150;
        
        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.width = Math.random() * 10 + 5 + 'px';
            confetti.style.height = Math.random() * 10 + 5 + 'px';
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
            
            confettiContainer.appendChild(confetti);
            
            // Animate confetti
            animateConfetti(confetti);
        }
        
        // Remove confetti after animation
        setTimeout(() => {
            document.body.removeChild(confettiContainer);
        }, 3000);
    }
    
    function animateConfetti(confetti) {
        let pos = -10;
        let velocity = Math.random() * 2 + 1;
        let angle = 0;
        let angleVelocity = (Math.random() - 0.5) * 0.2;
        let rotation = 0;
        
        const fall = () => {
            pos += velocity;
            rotation += angleVelocity;
            confetti.style.top = pos + 'px';
            confetti.style.transform = `rotate(${rotation}deg)`;
            
            if (pos < window.innerHeight) {
                requestAnimationFrame(fall);
            }
        };
        
        fall();
    }

    
    
    // Add button hover effects
    submitBtn.addEventListener('mouseenter', function() {
        this.style.background = 'linear-gradient(45deg, #ff3333, #bb0000)';
    });
    
    submitBtn.addEventListener('mouseleave', function() {
        this.style.background = 'linear-gradient(45deg, #ff4d4d, #cc0000)';
    });
});