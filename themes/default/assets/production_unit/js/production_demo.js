$(document).ready(function () {
    $('.dropdown-toggle').dropdown();
});

var countdownInterval;
var deliveryTime;

// Function to update the "Request Age" clock
function updateRequestAgeClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    // Pad single digit minutes and seconds with a leading zero
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    var timeString = hours + ':' + minutes + ':' + seconds;

    // Update "Request Age" with current time
    document.getElementById('currentTime1').textContent = timeString;
}

// Function to start the countdown timer
function startCountdown() {
    clearInterval(countdownInterval); // Clear any existing interval to avoid multiple timers

    countdownInterval = setInterval(function () {
        var currentTime = new Date();
        var timeDifference = deliveryTime.getTime() - currentTime.getTime();

        if (timeDifference <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('currentTime2').textContent = '00:00:00';
            return;
        }

        var hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

        // Pad single digit minutes and seconds with a leading zero
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        var countdownString = hours + ':' + minutes + ':' + seconds;

        // Update "Time to Delivery" with countdown time
        document.getElementById('currentTime2').textContent = countdownString;
    }, 1000);
}

// Function to show the input field for setting the delivery time
function toggleDeliveryTimeInput() {
    // Hide the set button and show the input field
    document.getElementById('setButton').style.display = 'none';
    document.getElementById('timeInputContainer').style.display = 'block';
    document.getElementById('timeDisplayContainer').style.display = 'none';
}

// Function to save the delivery time and start the countdown timer
function saveDeliveryTime() {
    var deliveryTimeInput = document.getElementById('deliveryTimeInput').value;

    if (deliveryTimeInput) {
        var [hours, minutes] = deliveryTimeInput.split(':');
        deliveryTime = new Date();
        deliveryTime.setHours(parseInt(hours));
        deliveryTime.setMinutes(parseInt(minutes));
        deliveryTime.setSeconds(0);

        // Hide the input field and show the countdown timer
        document.getElementById('timeInputContainer').style.display = 'none';
        document.getElementById('timeDisplayContainer').style.display = 'block';
        document.getElementById('setButton').style.display = 'block';

        // Start the countdown timer
        startCountdown();
    }
}

// Function to cancel setting the delivery time
function cancelSetDeliveryTime() {
    // Hide the input field and show the countdown timer without changing anything
    document.getElementById('timeInputContainer').style.display = 'none';
    document.getElementById('timeDisplayContainer').style.display = 'block';
    document.getElementById('setButton').style.display = 'block';
}

// Update the "Request Age" clock every second
setInterval(updateRequestAgeClock, 1000);