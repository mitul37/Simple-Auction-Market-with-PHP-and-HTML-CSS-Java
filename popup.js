// popup.js
setInterval(function() {
    fetch('check_new_bids.php')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach(bid => {
                    let popup = document.createElement('div');
                    popup.style.position = 'fixed';
                    popup.style.bottom = '20px';
                    popup.style.right = '20px';
                    popup.style.background = '#4caf50';
                    popup.style.color = '#fff';
                    popup.style.padding = '10px';
                    popup.style.borderRadius = '10px';
                    popup.style.zIndex = '9999';
                    popup.innerText = `🚀 ${bid.username} just bid ${bid.amount}৳ on ${bid.artwork}!`;
                    document.body.appendChild(popup);
                    setTimeout(() => { popup.remove(); }, 5000);
                });
            }
        });
}, 5000);
