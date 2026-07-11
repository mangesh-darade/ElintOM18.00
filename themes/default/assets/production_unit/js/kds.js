/**

 * KDS (Kitchen Display System) - Main JavaScript File

 * 

 * This file manages the complete KDS functionality including:

 * - Order and item state management

 * - Timer system for item preparation

 * - Visual feedback (colors, icons, animations)

 * - Data persistence using localStorage

 * - User interactions (select, prep, complete, cancel)

 * 

 * Business Logic:

 * - Orders have 3 header colors: White (not started), Blue (preparing), Green (all done)

 * - Items have 5 states: Not Started, Selected, Prep, Done, Cancelled

 * - Timers alert when ≤ 10 seconds remaining (red color, sound, flash)

 * - All data persists across page refreshes

 */



$(document).ready(function () {



    /* ================= GLOBAL STATE MANAGEMENT ================= */

    /**

     * These variables maintain the application state throughout the session.

     * They are used to track user selections, item states, and active timers.

     */



    let activeItem = null;           // Currently selected item object (for floating bar actions)

    let activeItemDiv = null;        // DOM reference to active item element (for visual updates)

    let activeOrderId = null;        // Currently active order ID (for context in actions)



    /**

     * itemStates: Tracks the state of each item across all orders

     * Key format: "orderId_itemId" (e.g., "123_456")

     * Value structure: {

     *   status: 'not_started' | 'prep' | 'ready' | 'done' | 'cancelled',

     *   timer: number (remaining seconds, can be negative),

     *   timerStart: timestamp (when prep started, for recalculation on refresh),

     *   prepTime: number (total prep time in seconds, default 300 = 5 minutes)

     * }

     * Why: Needed to persist item states across page refreshes and track timer progress

     */

    let itemStates = {};



    /**

     * timers: Tracks active setInterval IDs for each item's countdown timer

     * Key format: "orderId_itemId"

     * Value: intervalId (returned by setInterval)

     * Why: Needed to clear intervals when items are done/cancelled or page unloads

     */

    let timers = {};



    /**

     * alarmSound: Audio object for playing alert sounds

     * Why: Provides audio feedback when timer is ≤ 10 seconds (critical alert)

     */

    let alarmSound = null;



    /* ================= CUSTOM TIMER STATE MANAGEMENT ================= */

    /**

     * Custom timer state for the new bottom-right timer section

     * This is independent from the existing item timers

     */

    let customTimer = {

        isRunning: false,

        remainingSeconds: 0,

        totalSeconds: 0,

        intervalId: null,

        alarmIntervalId: null

    };



    /* ================= LOCALSTORAGE KEYS ================= */

    /**

     * These keys are used to persist data in browser localStorage.

     * Why: Allows KDS to maintain state across page refreshes, browser crashes, etc.

     */

    const STORAGE_KEY = 'kds_item_states';           // Stores all item states

    const ACTIVE_SELECTION_KEY = 'kds_active_selection';  // Stores currently selected item

    const CUSTOM_TIMER_KEY = 'kds_custom_timer';      // Stores custom timer state



    // Initialize alarm sound

    function initAlarmSound() {

        try {

            const audioContext = new (window.AudioContext || window.webkitAudioContext)();

            alarmSound = {

                play: function () {

                    const oscillator = audioContext.createOscillator();

                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);

                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = 800;

                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);

                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                    oscillator.start(audioContext.currentTime);

                    oscillator.stop(audioContext.currentTime + 0.5);

                }

            };

        } catch (e) {

            console.warn('Audio context not available');

            alarmSound = { play: function () { } };

        }

    }

    initAlarmSound();



    /* ================= CUSTOM CONFIRM DIALOG ================= */

    /**
     * Custom confirm dialog that matches the design shown in the image
     * Replaces browser's default confirm() function
     */
    function customConfirm(message, onConfirm, onCancel) {
        // Create modal HTML
        const modalHtml = `
            <div id="custom-confirm-modal" class="custom-confirm-overlay">
                <div class="custom-confirm-dialog">
                    <div class="custom-confirm-content">
                        <div class="custom-confirm-message">${message}</div>
                        <div class="custom-confirm-buttons">
                            <button class="custom-confirm-btn custom-confirm-cancel" type="button">Cancel</button>
                            <button class="custom-confirm-btn custom-confirm-yes" type="button">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('custom-confirm-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.getElementById('custom-confirm-modal');
        const cancelBtn = modal.querySelector('.custom-confirm-cancel');
        const yesBtn = modal.querySelector('.custom-confirm-yes');

        // Event handlers
        const handleCancel = () => {
            modal.remove();
            if (onCancel) onCancel();
        };

        const handleYes = () => {
            modal.remove();
            if (onConfirm) onConfirm();
        };

        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                handleCancel();
            }
        };

        // Add event listeners
        cancelBtn.addEventListener('click', handleCancel);
        yesBtn.addEventListener('click', handleYes);
        document.addEventListener('keydown', handleEscape);

        // Auto-focus on Yes button
        setTimeout(() => yesBtn.focus(), 100);

        // Cleanup function
        const cleanup = () => {
            cancelBtn.removeEventListener('click', handleCancel);
            yesBtn.removeEventListener('click', handleYes);
            document.removeEventListener('keydown', handleEscape);
        };

        // Store cleanup for later
        modal._cleanup = cleanup;
    }

    /**
     * Custom alert dialog for success messages
     * Replaces browser's default alert() function
     */
    function customAlert(message, callback) {
        // Create modal HTML
        const modalHtml = `
            <div id="custom-alert-modal" class="custom-alert-overlay">
                <div class="custom-alert-dialog">
                    <div class="custom-alert-content">
                        <div class="custom-alert-message">${message}</div>
                        <div class="custom-alert-buttons">
                            <button class="custom-alert-btn custom-alert-ok" type="button">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('custom-alert-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.getElementById('custom-alert-modal');
        const okBtn = modal.querySelector('.custom-alert-ok');

        // Event handlers
        const handleOk = () => {
            modal.remove();
            if (callback) callback();
        };

        const handleEscape = (e) => {
            if (e.key === 'Escape' || e.key === 'Enter') {
                handleOk();
            }
        };

        // Add event listeners
        okBtn.addEventListener('click', handleOk);
        document.addEventListener('keydown', handleEscape);

        // Auto-focus on OK button
        setTimeout(() => okBtn.focus(), 100);

        // Cleanup function
        const cleanup = () => {
            okBtn.removeEventListener('click', handleOk);
            document.removeEventListener('keydown', handleEscape);
        };

        // Store cleanup for later
        modal._cleanup = cleanup;
    }



    /* ================= LOCALSTORAGE PERSISTENCE FUNCTIONS ================= */



    /**

     * Loads all item states from localStorage and recalculates timers

     * Why: Restores state after page refresh. Timers are recalculated based on elapsed time

     *      to maintain accuracy even if page was closed for a while.

     * 

     * Timer Recalculation Logic:

     * - If item was in 'prep' state when page closed, we need to recalculate remaining time

     * - Formula: remaining = prepTime - (currentTime - timerStart)

     * - Negative values are OK (overdue items)

     */

    function loadStatesFromStorage() {

        try {

            const stored = localStorage.getItem(STORAGE_KEY);

            if (stored) {

                const parsed = JSON.parse(stored);



                // Restore states and recalculate timers for items that were in prep

                Object.keys(parsed).forEach(key => {

                    const state = parsed[key];



                    // If item was preparing when page closed, recalculate timer

                    if (state.status === 'prep' && state.timerStart) {

                        // Calculate elapsed time since timer started (in seconds)

                        const elapsed = Math.floor((Date.now() - state.timerStart) / 1000);

                        // Recalculate remaining time: prepTime - elapsed

                        state.timer = (state.prepTime || 300) - elapsed;

                        // Note: Timer can be negative (overdue), which is acceptable

                    }



                    // Restore state to global itemStates object

                    itemStates[key] = state;

                });

            }

        } catch (e) {

            // Handle corrupted localStorage data gracefully

            console.warn('Failed to load states from storage:', e);

        }

    }



    /**

     * Saves all item states to localStorage

     * Why: Persists state across page refreshes. Called periodically (every 2 seconds)

     *      and before critical operations to ensure data is saved.

     */

    function saveStatesToStorage() {

        try {

            // Store entire itemStates object as JSON string

            localStorage.setItem(STORAGE_KEY, JSON.stringify(itemStates));

        } catch (e) {

            // Handle localStorage quota exceeded or other errors

            console.warn('Failed to save states to storage:', e);

        }

    }



    /**

     * Saves the currently selected item to localStorage

     * Why: Restores user's selection after page refresh, improving UX.

     *      User doesn't lose their place if page accidentally refreshes.

     */

    function saveActiveSelection() {

        try {

            if (activeItem && activeOrderId) {

                // Store selection with timestamp for expiration check

                const selection = {

                    orderId: activeOrderId,

                    itemId: activeItem.id,

                    timestamp: Date.now()  // Used to expire old selections

                };

                localStorage.setItem(ACTIVE_SELECTION_KEY, JSON.stringify(selection));

            } else {

                // Clear selection if nothing is active (user deselected)

                localStorage.removeItem(ACTIVE_SELECTION_KEY);

            }

        } catch (e) {

            console.warn('Failed to save active selection:', e);

        }

    }



    /**

     * Loads the previously selected item from localStorage

     * Why: Restores user's selection after page refresh.

     * 

     * Expiration Logic:

     * - Selections older than 1 hour are considered stale and discarded

     * - This prevents restoring selections from previous sessions

     * 

     * @returns {Object|null} Selection object with orderId and itemId, or null if expired/not found

     */

    function loadActiveSelection() {

        try {

            const stored = localStorage.getItem(ACTIVE_SELECTION_KEY);

            if (stored) {

                const selection = JSON.parse(stored);



                // Check if selection is still valid (not too old - within 1 hour)

                const age = Date.now() - (selection.timestamp || 0);

                if (age < 3600000) { // 1 hour in milliseconds

                    return selection;

                } else {

                    // Selection is too old, remove it to prevent stale data

                    localStorage.removeItem(ACTIVE_SELECTION_KEY);

                }

            }

        } catch (e) {

            console.warn('Failed to load active selection:', e);

        }

        return null;

    }



    /**

     * Saves custom timer state to localStorage

     * Why: Persists timer across page refreshes

     */

    function saveCustomTimerToStorage() {

        try {

            // Store timer state with timestamp for elapsed time calculation

            const timerData = {

                ...customTimer,

                timestamp: Date.now()

            };

            localStorage.setItem(CUSTOM_TIMER_KEY, JSON.stringify(timerData));

        } catch (e) {

            console.warn('Failed to save custom timer to storage:', e);

        }

    }



    /**

     * Loads custom timer state from localStorage

     * Why: Restores timer state after page refresh

     */

    function loadCustomTimerFromStorage() {

        try {

            const stored = localStorage.getItem(CUSTOM_TIMER_KEY);

            if (stored) {

                const timerData = JSON.parse(stored);

                if (timerData.timestamp) {

                    // Calculate elapsed time since page was closed

                    const elapsedSeconds = Math.floor((Date.now() - timerData.timestamp) / 1000);



                    // Restore timer state

                    customTimer.isRunning = timerData.isRunning;

                    customTimer.totalSeconds = timerData.totalSeconds;
                    customTimer.remainingSeconds = timerData.remainingSeconds;



                    if (timerData.isRunning) {
                        customTimer.remainingSeconds = timerData.remainingSeconds - elapsedSeconds;

                        // Restart timer
                        customTimer.intervalId = setInterval(function () {
                            customTimer.remainingSeconds--;
                            updateCustomTimerDisplay();

                            if (customTimer.remainingSeconds === 5) {
                                startCustomAlarm();
                            }
                            if (customTimer.remainingSeconds === 0) {
                                startCustomAlarm();
                            }
                            saveCustomTimerToStorage();
                        }, 1000);

                        // If it's already in the alert zone (<= 5s), start alarm immediately
                        if (customTimer.remainingSeconds <= 5) {
                            startCustomAlarm();
                        }
                    } else {
                        // Restore state but don't start interval
                        customTimer.remainingSeconds = timerData.remainingSeconds;
                    }



                    updateCustomTimerDisplay();

                }

            }

        } catch (e) {

            console.warn('Failed to load custom timer from storage:', e);

        }

    }



    /* ================= CUSTOM TIMER FUNCTIONS ================= */



    /**

     * Shows the custom timer section when an order item is clicked

     * Why: Timer should only be visible when user has selected an item

     */

    function showCustomTimer() {

        const timerSection = document.getElementById('timer-section');

        if (timerSection) {

            timerSection.classList.remove('hidden');

        }



        // Show initial button state if timer is not running

        updateCustomTimerDisplay();

    }



    /**

     * Hides the custom timer section when no item is selected

     * Why: Timer should be hidden when user deselects items

     */

    function hideCustomTimer() {

        const timerSection = document.getElementById('timer-section');

        if (timerSection) {

            timerSection.classList.add('hidden');

        }

    }



    /**

     * Updates the timer display and color state based on remaining time

     * Handles visibility between initial button and expanded display

     */

    function updateCustomTimerDisplay() {

        const initialButton = document.getElementById('initial-timer-button');

        const expandedTimer = document.getElementById('expanded-timer');

        const timerDisplay = document.getElementById('custom-timer-display');

        const timerTime = document.getElementById('timer-time');



        if (!initialButton || !expandedTimer || !timerDisplay || !timerTime) return;



        // Update time display

        timerTime.textContent = formatTimer(customTimer.remainingSeconds);



        // Update color state
        timerDisplay.classList.remove('state-green', 'state-yellow', 'state-orange', 'state-red');

        if (customTimer.remainingSeconds > 0 || customTimer.isRunning || customTimer.remainingSeconds < 0) {
            // Show expanded timer, hide initial button
            initialButton.classList.add('hidden');
            expandedTimer.classList.remove('hidden');

            // Set color state
            if (customTimer.remainingSeconds <= 0) {
                timerDisplay.classList.add('state-red');
            } else if (customTimer.remainingSeconds <= 5) {
                timerDisplay.classList.add('state-orange');
            } else {
                timerDisplay.classList.add('state-yellow');
            }
        } else {
            // Show initial button, hide expanded timer
            initialButton.classList.remove('hidden');
            expandedTimer.classList.add('hidden');
        }

        // Refresh active item icon to check for custom timer alerts
        if (activeOrderId && activeItem) {
            const activeItemKey = `${activeOrderId}_${activeItem.id}`;
            const itemDiv = document.querySelector(`[data-item-key="${activeItemKey}"]`);
            if (itemDiv) {
                const itemAction = itemDiv.querySelector('.item-action');
                if (itemAction) {
                    const state = itemStates[activeItemKey];
                    if (state) {
                        const iconPath = getItemIcon(state, activeItemKey);
                        if (iconPath) {
                            const currentImg = itemAction.querySelector('img');
                            const fullIconUrl = APP.baseUrl + iconPath;
                            if (!currentImg || !currentImg.src.includes(iconPath)) {
                                itemAction.innerHTML = '';
                                const img = document.createElement('img');
                                img.src = fullIconUrl;
                                img.alt = state.status;
                                itemAction.appendChild(img);
                            }
                        } else {
                            itemAction.innerHTML = '';
                        }
                    }
                }
            }
        }

        // Update floating bar timer color based on custom timer
        const barTimerEl = document.querySelector('.bar-timer');
        if (barTimerEl) {
            if (customTimer.isRunning || customTimer.remainingSeconds < 0) {
                barTimerEl.classList.remove('timer-alerting', 'timer-overdue');
                if (customTimer.remainingSeconds <= 5) {
                    barTimerEl.classList.add('timer-overdue');
                }
            }
        }
    }



    /**

     * Starts the custom timer countdown

     * @param {number} minutes - Minutes to set

     * @param {number} seconds - Seconds to set

     */

    function startCustomTimer(minutes, seconds) {

        // Stop existing timer if running

        stopCustomTimer();



        // Calculate total seconds

        customTimer.totalSeconds = (minutes * 60) + seconds;

        customTimer.remainingSeconds = customTimer.totalSeconds;

        customTimer.isRunning = true;



        // Update display immediately
        updateCustomTimerDisplay();

        // If time is already low, start alarm
        if (customTimer.remainingSeconds <= 5) {
            startCustomAlarm();
        }

        // Start countdown interval
        customTimer.intervalId = setInterval(function () {
            customTimer.remainingSeconds--;
            updateCustomTimerDisplay();

            // Check if timer reached 5 seconds (start alarm)
            if (customTimer.remainingSeconds === 5) {
                startCustomAlarm();
            }

            // Check if timer reached 00:00 (keep running in negative)
            if (customTimer.remainingSeconds === 0) {
                // Ensure alarm is started if it hasn't already (e.g. if started with < 5s)
                startCustomAlarm();
            }

            // Save timer state to localStorage
            saveCustomTimerToStorage();
        }, 1000);



        // Save initial timer state

        saveCustomTimerToStorage();

    }



    /**

     * Stops the custom timer and alarm

     */

    function stopCustomTimer() {

        // Stop countdown interval

        if (customTimer.intervalId) {

            clearInterval(customTimer.intervalId);

            customTimer.intervalId = null;

        }



        // Stop alarm

        stopCustomAlarm();



        customTimer.isRunning = false;



        // Save timer state to localStorage

        saveCustomTimerToStorage();

    }



    /**

     * Resets the custom timer to initial state

     */

    function resetCustomTimer() {

        stopCustomTimer();

        customTimer.remainingSeconds = 0;

        customTimer.totalSeconds = 0;

        updateCustomTimerDisplay();



        // Save timer state to localStorage

        saveCustomTimerToStorage();

    }



    /**

     * Adds more time to the running timer

     * @param {number} additionalMinutes - Minutes to add

     * @param {number} additionalSeconds - Seconds to add

     */

    function addTimeToCustomTimer(additionalMinutes, additionalSeconds) {
        // Stop alarm on user action
        stopCustomAlarm();

        const totalAdditionalSeconds = (additionalMinutes * 60) + additionalSeconds;
        customTimer.remainingSeconds += totalAdditionalSeconds;
        customTimer.totalSeconds += totalAdditionalSeconds;



        // Stop alarm if it was ringing and time is now above the alert threshold
        // Note: stopCustomAlarm() was already called at the start of this function
        // because adding time is a user action.



        // Restart timer if it was stopped

        if (!customTimer.isRunning && customTimer.remainingSeconds > 0) {

            customTimer.isRunning = true;

            customTimer.intervalId = setInterval(function () {

                customTimer.remainingSeconds--;
                updateCustomTimerDisplay();

                if (customTimer.remainingSeconds === 5) {
                    startCustomAlarm();
                }

                if (customTimer.remainingSeconds === 0) {
                    startCustomAlarm();
                }
            }, 1000);

        }



        updateCustomTimerDisplay();



        // Save timer state to localStorage

        saveCustomTimerToStorage();

    }



    /**

     * Starts continuous alarm when timer reaches 00:00

     */

    function startCustomAlarm() {

        if (customTimer.alarmIntervalId) return; // Already ringing



        customTimer.alarmIntervalId = setInterval(function () {

            // Play alarm sound

            if (alarmSound && alarmSound.play) {

                alarmSound.play();

            }

        }, 1000); // Play every second

    }



    /**

     * Stops the continuous alarm

     */

    function stopCustomAlarm() {

        if (customTimer.alarmIntervalId) {

            clearInterval(customTimer.alarmIntervalId);

            customTimer.alarmIntervalId = null;

        }

    }



    /**

     * Shows a modal to set custom timer

     */

    function showCustomTimerModal() {

        // Create modal HTML

        const modalHtml = `

            <div id="custom-timer-modal" class="prep-time-modal" style="display: block;">

                <div class="prep-time-modal-content">

                    <div class="prep-time-modal-header">

                        <span>Set Timer</span>

                        <button class="prep-time-modal-close">✕</button>

                    </div>

                    <div class="prep-time-modal-body">

                        <div class="prep-time-input-group">

                            <label>Minutes:</label>

                            <input type="number" id="custom-timer-minutes" min="0" max="59" value="5" />

                        </div>

                        <div class="prep-time-input-group">

                            <label>Seconds:</label>

                            <input type="number" id="custom-timer-seconds" min="0" max="59" value="0" />

                        </div>

                    </div>

                    <div class="prep-time-modal-footer">

                        <button id="custom-timer-ok" class="btn-prep-time-ok">OK</button>

                        <button id="custom-timer-cancel" class="btn-prep-time-cancel">Cancel</button>

                    </div>

                </div>

            </div>

        `;



        // Remove existing modal if any

        const existingModal = document.getElementById('custom-timer-modal');

        if (existingModal) {

            existingModal.remove();

        }



        // Add modal to body

        document.body.insertAdjacentHTML('beforeend', modalHtml);



        const modal = document.getElementById('custom-timer-modal');
        const minutesInput = document.getElementById('custom-timer-minutes');
        const secondsInput = document.getElementById('custom-timer-seconds');

        // Add input validation to prevent negative values
        minutesInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
        
        secondsInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });


        // Position modal near timer section

        const timerSection = document.getElementById('timer-section');

        if (timerSection) {

            const rect = timerSection.getBoundingClientRect();

            modal.style.position = 'fixed';

            modal.style.top = (rect.top - 200) + 'px';

            modal.style.right = '20px';

            modal.style.left = 'auto';

            modal.style.bottom = 'auto';

            modal.style.transform = 'none';

        }



        // Focus on minutes input

        setTimeout(() => minutesInput.focus(), 100);



        // Event handlers

        const okHandler = function () {
            let minutes = parseInt(minutesInput.value) || 0;
            let seconds = parseInt(secondsInput.value) || 0;

            // Prevent negative values
            if (minutes < 0) minutes = 0;
            if (seconds < 0) seconds = 0;

            if (minutes === 0 && seconds === 0) {
                alert('Please enter a valid time');
                return;
            }

            startCustomTimer(minutes, seconds);

            modal.remove();

            cleanup();

        };



        const cancelHandler = function () {

            modal.remove();

            cleanup();

        };



        const closeHandler = function () {

            modal.remove();

            cleanup();

        };



        const escapeHandler = function (e) {

            if (e.key === 'Escape') {

                cancelHandler();

            } else if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {

                e.preventDefault();

                okHandler();

            }

        };



        function cleanup() {

            $('#custom-timer-ok').off('click', okHandler);

            $('#custom-timer-cancel').off('click', cancelHandler);

            $('.prep-time-modal-close').off('click', closeHandler);

            $(document).off('keydown', escapeHandler);

        }



        // Add event listeners

        $('#custom-timer-ok').on('click', okHandler);

        $('#custom-timer-cancel').on('click', cancelHandler);

        $('.prep-time-modal-close').on('click', closeHandler);

        $(document).on('keydown', escapeHandler);

    }



    /**

     * Shows modal to add more time

     */

    function showAddTimeModal() {

        // Create modal HTML

        const modalHtml = `

            <div id="add-time-modal" class="prep-time-modal" style="display: block;">

                <div class="prep-time-modal-content">

                    <div class="prep-time-modal-header">

                        <span>Add Time</span>

                        <button class="prep-time-modal-close">✕</button>

                    </div>

                    <div class="prep-time-modal-body">

                        <div class="prep-time-input-group">

                            <label>Minutes:</label>

                            <input type="number" id="add-time-minutes" min="0" max="59" value="1" />

                        </div>

                        <div class="prep-time-input-group">

                            <label>Seconds:</label>

                            <input type="number" id="add-time-seconds" min="0" max="59" value="0" />

                        </div>

                    </div>

                    <div class="prep-time-modal-footer">

                        <button id="add-time-ok" class="btn-prep-time-ok">Add</button>

                        <button id="add-time-cancel" class="btn-prep-time-cancel">Cancel</button>

                    </div>

                </div>

            </div>

        `;



        // Remove existing modal if any

        const existingModal = document.getElementById('add-time-modal');

        if (existingModal) {

            existingModal.remove();

        }



        // Add modal to body

        document.body.insertAdjacentHTML('beforeend', modalHtml);



        const modal = document.getElementById('add-time-modal');
        const minutesInput = document.getElementById('add-time-minutes');
        const secondsInput = document.getElementById('add-time-seconds');

        // Add input validation to prevent negative values
        minutesInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
        
        secondsInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });


        // Position modal near add button

        const addBtn = document.getElementById('timer-add-btn');

        if (addBtn) {

            const rect = addBtn.getBoundingClientRect();

            modal.style.position = 'fixed';

            modal.style.top = (rect.top - 200) + 'px';

            modal.style.right = '20px';

            modal.style.left = 'auto';

            modal.style.bottom = 'auto';

            modal.style.transform = 'none';

        }



        // Focus on minutes input

        setTimeout(() => minutesInput.focus(), 100);



        // Event handlers

        const okHandler = function () {
            let minutes = parseInt(minutesInput.value) || 0;
            let seconds = parseInt(secondsInput.value) || 0;

            // Prevent negative values
            if (minutes < 0) minutes = 0;
            if (seconds < 0) seconds = 0;

            if (minutes === 0 && seconds === 0) {
                alert('Please enter a valid time');
                return;
            }

            addTimeToCustomTimer(minutes, seconds);

            modal.remove();

            cleanup();

        };



        const cancelHandler = function () {

            modal.remove();

            cleanup();

        };



        const closeHandler = function () {

            modal.remove();

            cleanup();

        };



        const escapeHandler = function (e) {

            if (e.key === 'Escape') {

                cancelHandler();

            } else if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {

                e.preventDefault();

                okHandler();

            }

        };



        function cleanup() {

            $('#add-time-ok').off('click', okHandler);

            $('#add-time-cancel').off('click', cancelHandler);

            $('.prep-time-modal-close').off('click', closeHandler);

            $(document).off('keydown', escapeHandler);

        }



        // Add event listeners

        $('#add-time-ok').on('click', okHandler);

        $('#add-time-cancel').on('click', cancelHandler);

        $('.prep-time-modal-close').on('click', closeHandler);

        $(document).on('keydown', escapeHandler);

    }



    /* ================= INITIALIZATION ================= */

    /**

     * Initialize KDS on page load:

     * 1. Load persisted states from localStorage (restore previous session)

     * 2. Fetch current orders from backend

     * 3. Set up periodic auto-save (prevent data loss)

     */

    loadStatesFromStorage();  // Restore item states and recalculate timers

    loadCustomTimerFromStorage();  // Restore custom timer state

    fetchKDSData();           // Initial data load from backend



    // Note: Auto-refresh disabled by default (commented out) to reduce server load

    // // Uncomment if real-time updates are needed: setInterval(fetchKDSData, 5000);

    // setInterval(fetchKDSData, 2000);

    // // Auto-save states every 2 seconds to prevent data loss

    // setInterval(saveStatesToStorage, 2000);



    // // Auto-save active selection every 1 second (more frequent for better UX)

    // setInterval(saveActiveSelection, 1000);



    /**

     * Fetches KDS data (orders and items) from the backend

     * Why: Gets current orders that need to be displayed in KDS

     *      Called on initial load and can be called periodically for auto-refresh

     * 

     * Backend Endpoint: Production_Unit/get_kds_data

     * Returns: { orders: [...], items: {...} }

     */

    function fetchKDSData() {

        $.ajax({

            url: APP.baseUrl + 'Production_Unit/get_kds_data',

            type: 'GET',

            dataType: 'json',

            success: function (response) {

                // Render all orders and items on screen

                renderOrders(response);

                // Fix existing cancelled items to make them clickable (hotfix)
                setTimeout(() => {
                    fixCancelledItemsClickability();
                }, 300);

            },

            error: function (xhr) {

                // Log error for debugging (network issues, server errors, etc.)

                console.error('AJAX Error:', xhr.responseText);

            }

        });

    }



    /* ================= RENDER ORDERS ================= */



    function renderOrders(data) {

        const container = document.getElementById('orders-container');

        if (!container) return;



        // Preserve current active item selection

        const prevActiveKey = activeItem ? `${activeOrderId}_${activeItem.id}` : null;



        // Also try to load from localStorage

        const storedSelection = loadActiveSelection();

        const selectionToRestore = storedSelection || (prevActiveKey ? {

            orderId: activeOrderId,

            itemId: activeItem.id

        } : null);



        const orders = data.orders || [];

        const itemsByOrder = data.items || {};

        // Filter orders to show only today's orders

        const today = new Date();

        today.setHours(0, 0, 0, 0); // Set to start of day for comparison

        const todayOrders = orders.filter(order => {

            // Check if order has date field (could be date, created_at, added_date, etc.)

            const orderDate = order.date || order.created_at || order.added_date || order.order_date;

            if (!orderDate) {

                // If no date field, assume it's today's order (for backward compatibility)

                return true;

            }

            // Parse order date and compare with today

            const orderDateTime = new Date(orderDate);

            orderDateTime.setHours(0, 0, 0, 0); // Set to start of day for comparison

            return orderDateTime.getTime() === today.getTime();
        });

        // Sort orders by creation time (oldest first, newest last)
        todayOrders.sort((a, b) => {
            // Get full timestamp with time
            const timestampA = a.date || a.created_at || a.added_date || a.order_date;
            const timestampB = b.date || b.created_at || b.added_date || b.order_date;
            
            // Parse with full date and time
            const dateA = timestampA ? new Date(timestampA) : new Date('1970-01-01');
            const dateB = timestampB ? new Date(timestampB) : new Date('1970-01-01');
            
            // If no valid dates, sort by ID
            if (isNaN(dateA.getTime()) && isNaN(dateB.getTime())) {
                return (a.id || 0) - (b.id || 0);
            }
            if (isNaN(dateA.getTime())) return 1;
            if (isNaN(dateB.getTime())) return -1;
            
            // Sort by full timestamp (date + time)
            return dateA.getTime() - dateB.getTime();
        });

        const fragment = document.createDocumentFragment();

        todayOrders.forEach(order => {

            // Use consistent order ID (id for sales, id for suspended)

            const orderId = order.id;

            const items = itemsByOrder[orderId] || [];

            fragment.appendChild(createOrderTicket(order, items));

        });




        container.appendChild(fragment);



        // Restore active item selection if still exists

        if (selectionToRestore) {

            const itemKey = `${selectionToRestore.orderId}_${selectionToRestore.itemId}`;

            const itemDiv = document.querySelector(`[data-item-key="${itemKey}"]`);

            if (itemDiv && itemStates[itemKey]) {

                // Restore selection after a short delay to ensure DOM is ready

                setTimeout(() => {

                    const item = {

                        id: selectionToRestore.itemId

                    };

                    selectItem(selectionToRestore.orderId, item, itemDiv);

                }, 150);

            }

        }



        // Save states after rendering

        saveStatesToStorage();



        // Apply masonry layout after DOM is fully rendered

        setTimeout(() => {

            applyMasonryLayout();

        }, 200);

    }



    /* ================= ORDER TICKET ================= */



    function createOrderTicket(order, items) {

        const ticket = el('div', 'order-ticket');

        ticket.setAttribute('data-order-id', order.id);

        // Store order type to determine if it's a sale or suspended bill

        const orderType = order.order_source || (order.suspend_id ? 'suspended' : 'sale');

        ticket.setAttribute('data-order-type', orderType);



        ticket.appendChild(createHeader(order, items));

        ticket.appendChild(createItems(order.id, items));



        // Add "Serve & Complete" button if all items are done

        if (shouldShowServeButton(order.id, items)) {

            ticket.appendChild(createServeButton(order.id, orderType));

        }



        return ticket;

    }



    /* ================= HEADER ================= */



    function createHeader(order, items) {

        const header = el('div', 'order-header');



        const top = el('div', 'order-header-top');



        // Determine header color based on item states

        const headerColor = getOrderHeaderColor(order.id, items);

        top.className = `order-header-top ${headerColor}`;



        top.append(el('span', null, `#${order.display_invoice_no || order.invoice_no || order.id}`));

        top.append(el('div', 'order-time-badge', order.time || formatTime(new Date())));



        const bottom = el('div', 'order-header-bottom');



        // Table / order type

        const tableInfo = el('div', 'table-info');

        const iconPath = getOrderTypeIcon(order.order_type);



        if (iconPath) {

            const iconWrap = el('div', 'table-icon');

            const img = document.createElement('img');

            img.src = APP.baseUrl + iconPath;

            img.alt = order.order_type;

            iconWrap.appendChild(img);

            tableInfo.appendChild(iconWrap);



            // Show table number only for dine-in (just the number)

            if (order.order_type && order.order_type.toLowerCase().includes('dine')) {

                const tableNumber = order.table_id ? order.table_id : (order.display_invoice_no || '');

                tableInfo.append(el('div', 'table-number', tableNumber));

            }

        } else {

            tableInfo.append(

                el('div', 'table-number', order.order_type || '')

            );

        }

        bottom.appendChild(tableInfo);



        // Staff / Cook

        const staffCustomer = el('div', 'staff-customer');

        const staffName = order.staff_name || '-';  // staff_name

        const customer = order.customer; // customer name 

        staffCustomer.append(el('div', null, `S: ${staffName}`));

        staffCustomer.append(el('div', null, `C: ${customer}`));

        bottom.appendChild(staffCustomer);



        header.append(top, bottom);

        return header;

    }



    function getOrderHeaderColor(orderId, items) {

        // Check item states for this order

        let hasPrep = false;

        let allDone = true;

        let hasItems = items.length > 0;

        let hasActiveItems = false; // Items that are not cancelled or removed



        items.forEach(item => {

            const key = `${orderId}_${item.id}`;

            const state = itemStates[key];



            // Skip items that don't exist in state (removed items)

            if (!state) {

                return; // Item was removed, skip it

            }



            // Skip cancelled items in calculations

            if (state.status === 'cancelled') {

                return; // Skip cancelled items

            }



            hasActiveItems = true;



            if (state.status === 'not_started') {

                allDone = false;  // Order not ready if any item not started

            } else if (state.status === 'prep') {

                hasPrep = true;   // Order is being worked on

                allDone = false;  // Order not ready if any item still preparing

            } else if (state.status === 'ready') {

                // Item is ready - acceptable for green header and serve button

                // (allDone remains true, ready items count as "ready to serve")

            } else if (state.status === 'done') {

                // Item is done/served - acceptable for green header and serve button

                // (allDone remains true)

            } else {

                // Unknown state, assume not done

                allDone = false;

            }

        });



        // GREEN: All active items done OR ready (ready to serve)

        // Note: Items can be mix of ready and done states - both are acceptable

        if (hasItems && allDone && hasActiveItems) {

            return 'green';

        }



        // BLUE: At least one item in prep

        if (hasPrep) {

            return 'blue';

        }



        // WHITE: Receive Order (default)

        return 'white';

    }



    function getOrderTypeIcon(orderType) {

        if (!orderType) return null;



        const type = orderType.toLowerCase();



        if (type === 'delivery' || type === 'door delivery' || type === 'take away' || type === 'takeaway' || type === 'pickup' || type === 'pick up') {

            return 'assets/images/delivery.png';

        }



        if (type === 'dine in' || type === 'dine-in' || type === 'dinein') {

            return 'assets/images/diein.png';

        }



        return null;

    }



    function formatTime(date) {

        const hours = String(date.getHours()).padStart(2, '0');

        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${hours}:${minutes}`;

    }



    /* ================= ITEMS ================= */



    /**

     * Creates the items container for an order

     * 

     * Business Logic:

     * - Creates items for all items in the order

     * - Cancelled items are automatically hidden by createItem function

     * - Items marked as cancelled in state are skipped from display

     * 

     * @param {number} orderId - The order ID

     * @param {Array} items - Array of item objects

     * @returns {HTMLElement} Wrapper div containing all items

     */

    function createItems(orderId, items) {

        const wrapper = el('div', 'order-items');



        items.forEach(item => {

            // Create all items, including cancelled ones (they remain visible)

            wrapper.appendChild(createItem(orderId, item));

        });



        return wrapper;

    }



    /**

     * Creates a DOM element for a single order item

     * 

     * Business Logic:

     * - Each item has its own independent state (not_started, prep, done, cancelled)

     * - Items can be prepared independently of other items in the same order

     * - State persists across page refreshes via localStorage

     * - Timer is recalculated if item was in prep when page was closed

     * 

     * Why item-specific state:

     * - Kitchen staff work on items individually

     * - One item can be done while others are still preparing

     * - Allows granular tracking of order progress

     * 

     * @param {number} orderId - The order ID this item belongs to

     * @param {Object} item - Item object from backend (id, quantity, product_name, etc.)

     * @returns {HTMLElement} The created item DOM element

     */

    function createItem(orderId, item) {

        /* ================= ENSURE ITEM HAS ID ================= */

        // Ensure item.id exists - use database ID

        // Why: Item ID is critical for state tracking (used as key in itemStates)

        if (!item.id) {

            // Fallback: generate unique ID if database doesn't provide one

            // Format: "item_orderId_timestamp_random"

            item.id = `item_${orderId}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

        }



        /* ================= CREATE ITEM KEY ================= */

        // Create unique key for this item: "orderId_itemId"

        // Why: Used as key in itemStates object and data attributes

        const itemKey = `${orderId}_${item.id}`;



        /* ================= CREATE DOM ELEMENT ================= */

        const itemDiv = el('div', 'order-item');

        // Set data attributes for easy querying and state management

        itemDiv.setAttribute('data-item-key', itemKey);      // Unique identifier

        itemDiv.setAttribute('data-order-id', orderId);       // Parent order

        itemDiv.setAttribute('data-item-id', item.id);       // Item ID



        /* ================= INITIALIZE ITEM STATE ================= */

        // Initialize item state if not exists - EACH ITEM HAS ITS OWN STATE

        // Why: Items are independent - one item can be prep while another is done

        if (!itemStates[itemKey]) {

            itemStates[itemKey] = {

                status: 'not_started',      // Initial state: not yet started

                timer: null,                 // Timer value (null until prep starts)

                timerStart: null,            // Timestamp when prep started (for recalculation)

                prepTime: item.prep_time || 300  // Prep time in seconds (default 5 minutes)

            };

        }



        const state = itemStates[itemKey];



        /* ================= RECALCULATE TIMER IF IN PREP ================= */

        // If item was in prep when page closed, recalculate timer based on elapsed time

        // Why: Maintains timer accuracy after page refresh

        if (state.status === 'prep' && state.timerStart) {

            // Calculate elapsed time since prep started (in seconds)

            const elapsed = Math.floor((Date.now() - state.timerStart) / 1000);

            // Recalculate remaining time: prepTime - elapsed

            state.timer = (state.prepTime || 300) - elapsed;

        }



        /* ================= SET VISUAL STATE ================= */

        // Set item border color based on state (white/orange/green/red)

        // Why: Visual feedback for kitchen staff about item status

        updateItemVisualState(itemDiv, state);



        const content = el('div', 'order-item-content');



        // Create a container for quantity and name on the same line

        const mainItemLine = el('div', 'item-main-line');



        // Always show positive quantity

        const quantity = Math.abs(parseInt(item.quantity) || 1);

        mainItemLine.append(el('span', 'item-quantity', quantity + ' '));

        mainItemLine.append(

            el(

                'span',

                'item-name',

                item.product_name + (item.variant_name ? ` (${item.variant_name})` : '')

            )

        );



        content.appendChild(mainItemLine);



        // Add item modifications/notes if available

        if (item.comment && item.comment.trim()) {

            const modifiersDiv = el('div', 'item-modifiers');

            // Parse comment for modifications (format: "+ Extra lettuce, - No tomato" or similar)

            const modifiers = item.comment.split(',').map(m => m.trim()).filter(m => m);

            modifiers.forEach(modifier => {

                modifiersDiv.append(el('div', 'item-modifier', modifier));

            });

            content.appendChild(modifiersDiv);

        }



        itemDiv.appendChild(content);



        // Add item action icon

        const itemAction = el('div', 'item-action');
        const icon = getItemIcon(state, itemKey);
        if (icon) {

            const img = document.createElement('img');

            img.src = APP.baseUrl + icon;

            img.alt = state.status;

            itemAction.appendChild(img);

        }

        itemDiv.appendChild(itemAction);



        // Add prep time display if in prep state
        // REMOVED: Timer display is no longer needed
   /*
        if (state.status === 'prep' && state.timer !== null) {

            const timerDisplay = el('div', 'item-timer', formatTimer(state.timer));

            itemDiv.appendChild(timerDisplay);

        }
        */



        // Click event - add this before checking cancelled status so cancelled items can also be clicked
        itemDiv.addEventListener('click', function (e) {
            e.stopPropagation();
            selectItem(orderId, item, itemDiv);
        });

        // Start timer if item is in prep state
        if (state.status === 'prep' && state.timerStart) {
            // Restart timer to continue from where it left off
            startItemTimer(orderId, item.id, state);
        }

        // Cancelled items remain visible (not hidden)
        // They are marked as cancelled but displayed normally (no red border)
        if (state.status === 'cancelled') {
            // Update visual state (white border, no special color)
            updateItemVisualState(itemDiv, state);
            // Clear icon for cancelled items
            const itemAction = itemDiv.querySelector('.item-action');
            if (itemAction) {
                itemAction.innerHTML = '';
            }
        }

        return itemDiv;

    }



    /**

     * Updates the visual state (border color) of an item based on its status

     * 

     * Border Color Logic:

     * - White: Not started

     * - Orange: Preparing (with timer)

     * - Red: Alerting (timer ≤ 10s) or Overdue (negative timer)

     * - Green: Ready or Done

     * 

     * @param {HTMLElement} itemDiv - The item DOM element

     * @param {Object} state - The item state object

     */

    /**

     * Updates the visual state (border color) of an item based on its status

     * 

     * Border Color Logic:

     * - White: Not started or Cancelled

     * - Orange: Preparing (with timer)

     * - Red: Alerting (timer ≤ 10s) or Overdue (negative timer)

     * - Green: Ready or Done

     * 

     * @param {HTMLElement} itemDiv - The item DOM element

     * @param {Object} state - The item state object

     */

    function updateItemVisualState(itemDiv, state) {

        // Remove all state classes

        // Remove only status and alert classes, preserve 'active' selection class
        itemDiv.classList.remove('status-white', 'status-orange', 'status-red', 'status-green', 'status-yellow', 'alerting', 'status-ready');



        if (state.status === 'cancelled') {

            // Cancelled - white border (no special color, normal appearance)

            // Why: Cancelled items remain visible but without red border styling

            itemDiv.classList.add('status-white');

        } else if (state.status === 'ready') {

            // Ready - green border + flashing animation

            itemDiv.classList.add('status-green', 'status-ready');

        } else if (state.status === 'done') {

            // Done - green border

            itemDiv.classList.add('status-green');

        } else if (state.status === 'prep') {

            // Prep state - color depends on timer (remaining or overdue)

            const timerValue = state.timer !== null ? Math.abs(state.timer) : 0;
            const minutesValue = timerValue / 60;

            if (minutesValue > 10) {
                // > 10 min - red border
                itemDiv.classList.add('status-red');
            } else if (minutesValue > 5) {
                // ≤ 10 min - orange border
                itemDiv.classList.add('status-orange');
            } else {
                // ≤ 5 min - yellow border
                itemDiv.classList.add('status-yellow');
            }

            // Maintain alerting animation for the last 10 seconds of prep
            if (state.timer !== null && state.timer <= 10 && state.timer > 0) {
                itemDiv.classList.add('alerting');
            }

        } else {

            // Not started - white border

            itemDiv.classList.add('status-white');

        }

    }



    /**

     * Returns the appropriate icon path based on item status

     * 

     * Icon Logic:

     * - Not Started: No icon (null)

     * - Prep: Frying pan icon (cooking in progress)

     * - Prep (Alerting ≤10s): Alarm bell icon (urgent attention needed)

     * - Ready: Food tray icon (ready to serve)

     * - Done: Checkmark icon (completed/served)

     * 

     * @param {Object} state - Item state object

     * @returns {string|null} Icon path or null

     */

    /**

     * Returns the appropriate icon path based on item status

     * 

     * Icon Logic (as per user requirement):

     * - Not Started: No icon (null)

     * - Prep: Frying pan icon (cooking in progress)

     * - Prep (Alerting ≤10s): Alarm bell icon (urgent attention needed)

     * - Ready: Food tray icon (ready to serve)

     * - Done: Checkmark icon (completed/served)

     * 

     * Why different icons for each state:

     * - Provides clear visual feedback to kitchen staff

     * - Easy to identify item status at a glance

     * - Icons match the workflow: Prep → Ready → Serve

     * 

     * @param {Object} state - Item state object

     * @returns {string|null} Icon path or null

     */

    function getItemIcon(state, itemKey) {
        if (state.status === 'done') {
            // Item is completed/served - show checkmark icon
            // Why: Indicates item is finished and served
            return 'assets/images/Check_mark.png';
        } else if (state.status === 'ready') {
            // Ready state - show bell icon as requested by user
            // Why: Visual indicator that item is ready for pickup/serving
            return 'assets/images/alarm_bell.png';
        } else if (state.status === 'prep') {
            // Check if this is the active item and the custom timer is alerting
            if (itemKey && activeOrderId && activeItem && `${activeOrderId}_${activeItem.id}` === itemKey) {
                if ((customTimer.isRunning || customTimer.remainingSeconds < 0) && customTimer.remainingSeconds <= 5) {
                    return 'assets/images/alarm_bell.png';
                }
            }
            // Normal prep state - show frying pan icon
            // Why: Indicates item is currently being prepared/cooked
            return 'assets/images/frying_pan.png';
        }

        // Not started - no icon

        // Why: No action needed yet, no icon reduces visual clutter

        return null;

    }



    function formatTimer(seconds) {
        const absSeconds = Math.abs(seconds);
        const mins = Math.floor(absSeconds / 60);
        const secs = absSeconds % 60;
        const sign = seconds < 0 ? '-' : '';
        const separator = seconds < 0 ? '.' : ':';
        return `${sign}${String(mins).padStart(2, '0')}${separator}${String(secs).padStart(2, '0')}`;
    }



    /* ================= ITEM SELECTION ================= */



    function selectItem(orderId, item, itemDiv) {

        // Remove active class from all items

        document.querySelectorAll('.order-item').forEach(i => i.classList.remove('active'));



        // Add active class to clicked item

        itemDiv.classList.add('active');



        activeItem = item;

        activeItemDiv = itemDiv;

        activeOrderId = orderId;



        const itemKey = `${orderId}_${item.id}`;

        const state = itemStates[itemKey];



        // Show floating bar with appropriate buttons

        showFloatingBar(orderId, item, state);



        // Show custom timer when item is selected

        showCustomTimer();



        // Save active selection to localStorage

        saveActiveSelection();

    }



    /* ================= FLOATING ACTION BAR ================= */



    function showFloatingBar(orderId, item, state) {

        const floatingBar = document.getElementById('floating-bar');

        if (!floatingBar) return;



        floatingBar.classList.remove('hidden', 'ready-state');
        if (state.status === 'ready') {
            floatingBar.classList.add('ready-state');
        }



        // Update prep/ready/serve button based on current status with icons

        const prepBtn = document.getElementById('prep-btn');

        const prepBtnIcon = document.getElementById('prep-btn-icon');

        const prepBtnText = document.getElementById('prep-btn-text');



        if (prepBtn && prepBtnIcon && prepBtnText) {

            if (state.status === 'prep') {

                // Item is preparing - show "Ready" button with food tray icon

                prepBtn.className = 'btn ready';

                prepBtnIcon.src = APP.baseUrl + 'assets/images/Food_tray.png';

                prepBtnIcon.alt = 'Ready';

                prepBtnText.textContent = 'Ready';

            } else if (state.status === 'ready') {

                // Item is ready - show "Serve" button with checkmark icon

                prepBtn.className = 'btn serve';

                prepBtnIcon.src = APP.baseUrl + 'assets/images/Check_mark.png';

                prepBtnIcon.alt = 'Serve';

                prepBtnText.textContent = 'Serve';

            } else if (state.status === 'cancelled') {

                // Item was cancelled - show "Prep" button with frying pan icon so it can be prepared again

                prepBtn.className = 'btn prep';

                prepBtnIcon.src = APP.baseUrl + 'assets/images/frying_pan.png';

                prepBtnIcon.alt = 'Prep';

                prepBtnText.textContent = 'Prep';

            } else {

                // Item not started - show "Prep" button with frying pan icon

                prepBtn.className = 'btn prep';

                prepBtnIcon.src = APP.baseUrl + 'assets/images/frying_pan.png';

                prepBtnIcon.alt = 'Prep';

                prepBtnText.textContent = 'Prep';

            }

        }



        // Update timer display with color states

        const timerDisplay = document.getElementById('cook-timer');

        const barTimerEl = floatingBar.querySelector('.bar-timer');

        if (timerDisplay && barTimerEl) {

            if (state.status === 'prep' && state.timer !== null) {

                // Item is preparing - show countdown timer

                timerDisplay.textContent = formatTimer(state.timer);

                barTimerEl.classList.remove('timer-alerting', 'timer-overdue', 'timer-warning', 'timer-ready');

                const timerVal = Math.abs(state.timer);
                const minsVal = timerVal / 60;

                if (minsVal > 10) {
                    barTimerEl.classList.add('timer-overdue');
                } else if (minsVal > 5) {
                    barTimerEl.classList.add('timer-alerting');
                } else {
                    barTimerEl.classList.add('timer-warning');
                }

            } else if (state.status === 'ready') {

                // Item is ready - show negative elapsed waiting timer as per user request (Image 2)
                const elapsedReady = state.readyStart ? Math.floor((Date.now() - state.readyStart) / 1000) : 0;
                timerDisplay.textContent = formatTimer(-elapsedReady);
                
                barTimerEl.classList.remove('timer-alerting', 'timer-warning', 'timer-ready');
                barTimerEl.classList.add('timer-overdue'); // Red text for negative count

            } else if (state.status === 'done') {

                // Item is done - show negative time if overdue, or "Done"

                if (state.timer !== null && state.timer < 0) {

                    // Show negative time in red

                    timerDisplay.textContent = formatTimer(state.timer);

                    barTimerEl.classList.remove('timer-ready');

                    barTimerEl.classList.add('timer-overdue');

                } else {

                    timerDisplay.textContent = 'Done';

                    barTimerEl.classList.remove('timer-alerting', 'timer-overdue', 'timer-ready');

                }

            } else if (state.status === 'cancelled') {

                // Item was cancelled - show prep time so user knows how long it will take when re-prepped

                timerDisplay.textContent = formatTimer(state.prepTime || 300);

                barTimerEl.classList.remove('timer-alerting', 'timer-overdue', 'timer-ready');

            } else {

                // Not started - show prep time (Cook Time)

                timerDisplay.textContent = formatTimer(state.prepTime || 300);

                barTimerEl.classList.remove('timer-alerting', 'timer-overdue', 'timer-ready');

            }

        }



        // Make timer area clickable to set time

        const barTimer = floatingBar.querySelector('.bar-timer');

        if (barTimer) {

            // Remove existing click handler by cloning

            const newBarTimer = barTimer.cloneNode(true);

            barTimer.parentNode.replaceChild(newBarTimer, barTimer);



            // Add click handler to set prep time

            newBarTimer.style.cursor = 'pointer';

            newBarTimer.title = 'Click to set prep time';

            newBarTimer.addEventListener('click', function (e) {

                e.stopPropagation();

                setItemPrepTime(orderId, item.id, state);

            });

        }



        // Remove existing event listeners by cloning

        const newPrepBtn = prepBtn.cloneNode(true);

        prepBtn.parentNode.replaceChild(newPrepBtn, prepBtn);



        // Update references after cloning

        const updatedPrepBtn = document.getElementById('prep-btn');

        const updatedPrepBtnIcon = document.getElementById('prep-btn-icon');

        const updatedPrepBtnText = document.getElementById('prep-btn-text');



        const newCancelBtn = document.getElementById('cancel-btn').cloneNode(true);

        document.getElementById('cancel-btn').parentNode.replaceChild(newCancelBtn, document.getElementById('cancel-btn'));



        // Add event listeners

        updatedPrepBtn.addEventListener('click', function () {

            const currentState = itemStates[`${orderId}_${item.id}`];

            if (currentState) {

                if (currentState.status === 'prep') {

                    // Item is preparing - mark as ready

                    markItemReady(orderId, item.id);

                } else if (currentState.status === 'ready') {

                    // Item is ready - mark as done/served

                    markItemServed(orderId, item.id);

                } else {

                    // Item not started - start prep

                    startItemPrep(orderId, item.id);

                }

            }

        });



        newCancelBtn.addEventListener('click', function () {

            cancelItem(orderId, item.id);

        });

    }



    function hideFloatingBar() {

        const floatingBar = document.getElementById('floating-bar');

        if (floatingBar) {

            floatingBar.classList.add('hidden');

        }

        document.querySelectorAll('.order-item').forEach(i => i.classList.remove('active'));

        activeItem = null;

        activeItemDiv = null;

        activeOrderId = null;



        // Hide custom timer when no item is selected

        hideCustomTimer();



        // Clear active selection from localStorage

        saveActiveSelection();

    }



    /* ================= ITEM ACTIONS ================= */



    function setItemPrepTime(orderId, itemId, state) {

        // Get current prep time

        const currentTotalSeconds = state.prepTime || 300;

        const currentMinutes = Math.floor(currentTotalSeconds / 60);

        const currentSeconds = currentTotalSeconds % 60;



        // Show custom modal

        showPrepTimeModal(currentMinutes, currentSeconds, function (minutes, seconds) {

            // Calculate total seconds

            const prepTimeSeconds = (parseInt(minutes) || 0) * 60 + (parseInt(seconds) || 0);



            if (prepTimeSeconds < 0) {

                alert('Please enter a valid time (0 or greater)');

                return;

            }



            // Update prep time

            state.prepTime = prepTimeSeconds;



            // If item is already in prep, restart timer with new time

            if (state.status === 'prep') {

                // Stop existing timer

                const itemKey = `${orderId}_${itemId}`;

                if (timers[itemKey]) {

                    clearInterval(timers[itemKey]);

                    delete timers[itemKey];

                }



                // Restart timer with new prep time

                state.timerStart = Date.now();

                state.timer = prepTimeSeconds;

                startItemTimer(orderId, itemId, state);

            }



            // Update timer display in floating bar

            const timerDisplay = document.getElementById('cook-timer');

            if (timerDisplay) {

                timerDisplay.textContent = formatTimer(prepTimeSeconds);

            }



            // Show confirmation feedback

            const barTimer = document.querySelector('.bar-timer');

            if (barTimer) {

                const originalBg = barTimer.style.backgroundColor;

                // barTimer.style.backgroundColor = '#00C896';

                barTimer.style.color = '#FFFFFF';

                setTimeout(() => {

                    barTimer.style.backgroundColor = originalBg || '';

                    barTimer.style.color = '';

                }, 800);

            }



            saveStatesToStorage();

            saveActiveSelection();

        });

    }



    function showPrepTimeModal(currentMinutes, currentSeconds, callback) {

        const modal = document.getElementById('prep-time-modal');

        const modalContent = modal ? modal.querySelector('.prep-time-modal-content') : null;

        const minutesInput = document.getElementById('prep-time-minutes');

        const secondsInput = document.getElementById('prep-time-seconds');

        // Add input validation to prevent negative values
        minutesInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
        
        secondsInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });


        if (!modal || !modalContent || !minutesInput || !secondsInput) return;



        // Set current values

        minutesInput.value = currentMinutes;

        secondsInput.value = currentSeconds;



        // Position modal near timer icon (small popup style)

        const barTimer = document.querySelector('.bar-timer');

        if (barTimer) {

            const rect = barTimer.getBoundingClientRect();



            // Position popup above or below timer, centered horizontally

            const popupWidth = 260;

            const popupHeight = 180;



            // Calculate position relative to viewport

            let top = rect.bottom + 10;

            let left = rect.left + (rect.width / 2) - (popupWidth / 2);



            // Adjust if popup goes off screen horizontally

            if (left < 10) {

                left = 10;

            } else if (left + popupWidth > window.innerWidth - 10) {

                left = window.innerWidth - popupWidth - 10;

            }



            // If popup would go below screen, show above timer

            if (top + popupHeight > window.innerHeight - 10) {

                top = rect.top - popupHeight - 10;

                // Ensure it doesn't go above screen

                if (top < 10) {

                    top = 10;

                }

            }



            // Use fixed positioning (relative to viewport, not document)

            modal.style.top = top + 'px';

            modal.style.left = left + 'px';

            modal.style.right = 'auto';

            modal.style.bottom = 'auto';

            modal.style.transform = 'none'; // Reset any previous transform

        }



        // Show modal

        modal.classList.remove('hidden');



        // Focus on minutes input

        setTimeout(() => minutesInput.focus(), 100);



        // Make modal draggable

        let isDragging = false;

        let currentX;

        let currentY;

        let initialX;

        let initialY;

        let xOffset = 0;

        let yOffset = 0;



        const header = modalContent.querySelector('.prep-time-modal-header');

        if (header) {

            header.addEventListener('mousedown', dragStart);

            document.addEventListener('mousemove', drag);

            document.addEventListener('mouseup', dragEnd);

        }



        function dragStart(e) {

            if (e.target.classList.contains('prep-time-modal-close')) {

                return; // Don't drag when clicking close button

            }



            if (e.target === header || header.contains(e.target)) {

                isDragging = true;



                // Get current modal position

                const modalRect = modal.getBoundingClientRect();

                initialX = e.clientX - modalRect.left;

                initialY = e.clientY - modalRect.top;



                e.preventDefault();

            }

        }



        function drag(e) {

            if (isDragging) {

                e.preventDefault();



                // Calculate new position

                currentX = e.clientX - initialX;

                currentY = e.clientY - initialY;



                // Keep popup within viewport bounds

                const popupWidth = 260;

                const popupHeight = 180;



                if (currentX < 0) currentX = 0;

                if (currentY < 0) currentY = 0;

                if (currentX + popupWidth > window.innerWidth) {

                    currentX = window.innerWidth - popupWidth;

                }

                if (currentY + popupHeight > window.innerHeight) {

                    currentY = window.innerHeight - popupHeight;

                }



                // Update position

                modal.style.left = currentX + 'px';

                modal.style.top = currentY + 'px';

                modal.style.transform = 'none';

            }

        }



        function dragEnd(e) {

            if (isDragging) {

                isDragging = false;

            }

        }



        // OK button handler

        const okHandler = function () {
            let minutes = parseInt(minutesInput.value) || 0;
            let seconds = parseInt(secondsInput.value) || 0;

            // Prevent negative values
            if (minutes < 0) minutes = 0;
            if (seconds < 0) seconds = 0;

            modal.classList.add('hidden');

            // Reset position for next time

            modal.style.transform = '';

            modal.style.top = '';

            modal.style.left = '';

            if (callback) callback(minutes, seconds);

            cleanup();

        };



        // Cancel button handler

        const cancelHandler = function () {

            modal.classList.add('hidden');

            // Reset position for next time

            modal.style.transform = '';

            modal.style.top = '';

            modal.style.left = '';

            cleanup();

        };



        // Cleanup function

        function cleanup() {

            $('#prep-time-ok').off('click', okHandler);

            $('#prep-time-cancel').off('click', cancelHandler);

            $('.prep-time-modal-close').off('click', cancelHandler);

            $(document).off('keydown', escapeHandler);

            if (header) {

                header.removeEventListener('mousedown', dragStart);

                document.removeEventListener('mousemove', drag);

                document.removeEventListener('mouseup', dragEnd);

            }

        }



        // Escape key handler

        const escapeHandler = function (e) {

            if (e.key === 'Escape') {

                cancelHandler();

            } else if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {

                e.preventDefault();

                okHandler();

            }

        };



        // Remove old handlers and add new ones

        cleanup();

        $('#prep-time-ok').on('click', okHandler);

        $('#prep-time-cancel').on('click', cancelHandler);

        $('.prep-time-modal-close').on('click', cancelHandler);

        $(document).on('keydown', escapeHandler);

    }



    /**

     * Starts preparation for a specific item

     * 

     * Business Logic:

     * - Changes item status from 'not_started' to 'prep'

     * - Starts countdown timer from prepTime (default 5 minutes)

     * - Updates visual state (orange border, cooking pan icon)

     * - Changes order header to blue (if not already)

     * 

     * Why item-specific:

     * - Each item in an order can be prepared independently

     * - Only the selected item should enter prep state

     * - Other items in the same order remain unchanged

     * 

     * @param {number} orderId - The order ID

     * @param {number} itemId - The item ID to start prep for

     */

    function startItemPrep(orderId, itemId) {

        const itemKey = `${orderId}_${itemId}`;

        const state = itemStates[itemKey];



        // Safety check: state must exist

        if (!state) {

            return;

        }



        /* ================= UPDATE ITEM STATE ================= */

        // Update state ONLY for this specific item (not the entire order)

        state.status = 'prep';                    // Change status to preparing

        state.timerStart = Date.now();            // Record when prep started (for timer recalculation on refresh)

        state.timer = state.prepTime || 300;      // Set timer to prep time (default 5 minutes = 300 seconds)



        /* ================= START TIMER ================= */

        // Start countdown timer for this specific item only

        // Why: Timer runs independently for each item

        startItemTimer(orderId, itemId, state);



        /* ================= UPDATE VISUAL STATE ================= */

        // Update UI - ONLY this specific item (orange border, icon, timer display)

        updateItemInOrder(orderId, itemId);



        /* ================= UPDATE ORDER HEADER ================= */

        // Update order header color (this is correct - header changes when ANY item enters prep)

        // Why: Header turns blue when any item starts preparing (business rule)

        updateOrderHeaderColor(orderId);



        /* ================= UPDATE FLOATING BAR ================= */

        // Update floating bar if this is the currently selected item

        // Why: Floating bar shows timer and "Ready to Serve" button for active item

        if (activeItem && activeItem.id === itemId) {

            showFloatingBar(orderId, activeItem, state);

        }



        /* ================= PERSIST STATE ================= */

        // Save to localStorage to persist across page refreshes

        saveStatesToStorage();

        saveActiveSelection();

    }



    /**

     * Manages the countdown timer for an item in prep state

     * 

     * Business Logic:

     * - Timer counts down from prepTime to 0

     * - Updates every second

     * - Shows alert (red color, sound, flash) when ≤ 10 seconds

     * - Can go negative (overdue) - shows as red

     * - Stops automatically after 1 hour overdue (prevents memory leaks)

     * 

     * Timer Color States:

     * - Normal (> 10s): Orange (#FF9A63)

     * - Alerting (≤ 10s): Red (#FF3B47) - plays alarm sound

     * - Overdue (< 0): Red (#FF3B47)

     * 

     * Why calculate from elapsed time:

     * - More accurate than decrementing (accounts for browser tab inactivity)

     * - Recalculates correctly after page refresh

     * - Handles negative values (overdue items)

     * 

     * @param {number} orderId - The order ID

     * @param {number} itemId - The item ID

     * @param {Object} state - The item state object (modified in place)

     */

    function startItemTimer(orderId, itemId, state) {

        const itemKey = `${orderId}_${itemId}`;



        /* ================= CLEAR EXISTING TIMER ================= */

        // Clear existing timer if any (prevents duplicate timers)

        // Why: If function is called multiple times, we don't want multiple intervals

        if (timers[itemKey]) {

            clearInterval(timers[itemKey]);

        }



        /* ================= TIMER UPDATE FUNCTION ================= */

        /**

         * This function runs every second to update the timer display

         * Why inline function: Needs access to closure variables (orderId, itemId, state, itemKey)

         */

        const updateTimer = function () {

            // Safety check: timerStart must exist

            if (!state.timerStart && state.status !== 'ready') return;



            /* ================= CALCULATE REMAINING TIME ================= */

            // Calculate elapsed time since timer started (in seconds)

            if (state.status === 'prep') {
                const elapsed = Math.floor((Date.now() - state.timerStart) / 1000);
                // Calculate remaining time: prepTime - elapsed
                // Note: Can be negative (overdue), which is acceptable
                state.timer = (state.prepTime || 300) - elapsed;
            }



            /* ================= UPDATE ITEM DISPLAY ================= */

            // Find item element in DOM

            const itemDiv = document.querySelector(`[data-item-key="${itemKey}"]`);

            if (itemDiv) {

                // Update visual state (border color, etc.)

                updateItemVisualState(itemDiv, state);



                /* ================= UPDATE ICON ================= */
                // Update item icon (pan → bell → etc.)
                const itemAction = itemDiv.querySelector('.item-action');
                if (itemAction) {
                    const iconPath = getItemIcon(state, itemKey);
                    if (iconPath) {
                        const fullIconUrl = APP.baseUrl + iconPath;
                        const currentImg = itemAction.querySelector('img');

                        // Update if no image or if source changed
                        if (!currentImg || !currentImg.src.includes(iconPath)) {
                            itemAction.innerHTML = '';
                            const img = document.createElement('img');
                            img.src = fullIconUrl;
                            img.alt = state.status;
                            itemAction.appendChild(img);
                        }
                    } else {
                        itemAction.innerHTML = '';
                    }
                }



                /* ================= UPDATE TIMER DISPLAY IN ITEM ================= */

                // Show/hide timer display in item row
                // REMOVED: Timer display is no longer needed
                /*
                // Update timer display in item (only show timer during prep state)

                let timerDisplay = itemDiv.querySelector('.item-timer');

                if (state.status === 'prep') {
                    // Show countdown timer during prep

                    if (!timerDisplay) {

                        // Create timer display if it doesn't exist

                        timerDisplay = el('div', 'item-timer', formatTimer(state.timer));

                        itemDiv.appendChild(timerDisplay);

                    } else {

                        // Update existing timer display

                        timerDisplay.textContent = formatTimer(state.timer);
                    }
                } else if (state.status === 'ready') {
                    // Show negative elapsed time during ready state as requested
                    const elapsedReady = state.readyStart ? Math.floor((Date.now() - state.readyStart) / 1000) : 0;
                    if (!timerDisplay) {
                        timerDisplay = el('div', 'item-timer', formatTimer(-elapsedReady));
                        itemDiv.appendChild(timerDisplay);
                    } else {
                        timerDisplay.textContent = formatTimer(-elapsedReady);
                    }
                } else if (timerDisplay) {

                    // Remove timer display when item is ready or done (no longer preparing)

                    timerDisplay.remove();

                }
                */



                // Alert when timer is ≤ 5 seconds (critical time)
                // Why: Gives kitchen staff warning that item needs attention soon
                if (state.status === 'prep' && state.timer <= 5 && state.timer > 0) {

                    if (!itemDiv.classList.contains('alerting')) {

                        // Add flashing animation class

                        itemDiv.classList.add('alerting');

                        // Play alarm sound (only once, not every second)
                        try {
                            if (alarmSound && typeof alarmSound.play === 'function') {
                                alarmSound.play();
                            }
                        } catch (e) {
                            console.error('Error playing alarm:', e);
                        }

                    }

                }



                /* ================= UPDATE FLOATING BAR TIMER ================= */

                // Update timer in floating bar if this is the active item

                // Why: Floating bar shows timer for the currently selected item

                if (activeItem && activeItem.id === itemId) {

                    const timerDisplay = document.getElementById('cook-timer');

                    const barTimer = document.querySelector('.bar-timer');

                    if (timerDisplay && barTimer) {
                        // Only show timer during prep state
                        if (state.status === 'prep') {
                            // Update timer text (countdown)
                            timerDisplay.textContent = formatTimer(state.timer);
                            barTimer.classList.remove('timer-alerting', 'timer-overdue', 'timer-warning', 'timer-ready');
                            const tVal = Math.abs(state.timer);
                            const mVal = tVal / 60;
                            if (mVal > 10) {
                                barTimer.classList.add('timer-overdue');
                            } else if (mVal > 5) {
                                barTimer.classList.add('timer-alerting');
                            } else {
                                barTimer.classList.add('timer-warning');
                            }
                        } else if (state.status === 'ready') {
                            // Update timer text (negative elapsed time since ready)
                            const elapsedReady = state.readyStart ? Math.floor((Date.now() - state.readyStart) / 1000) : 0;
                            timerDisplay.textContent = formatTimer(-elapsedReady);
                            barTimer.classList.remove('timer-alerting', 'timer-warning', 'timer-ready');
                            barTimer.classList.add('timer-overdue');
                        }
                    }

                }

            }



            /* ================= AUTO-STOP TIMER ================= */

            // Stop timer after 1 hour overdue to prevent memory leaks

            // Why: If item is forgotten, timer shouldn't run forever

            if (state.timer < -3600) { // 1 hour = 3600 seconds

                clearInterval(timers[itemKey]);

                delete timers[itemKey];

            }

        };



        /* ================= START TIMER ================= */

        // Update immediately (don't wait 1 second for first update)

        updateTimer();



        // Update every second (1000ms)

        // Store interval ID so we can clear it later

        timers[itemKey] = setInterval(updateTimer, 1000);

    }



    /**

     * Marks item as ready to serve (transition from prep to ready)

     * 

     * Business Logic:

     * - Stops the prep timer

     * - Changes status from 'prep' to 'ready'

     * - Updates icon to food tray

     * - Updates border color (orange → green)

     * - Button changes to "Serve"

     * 

     * @param {number} orderId - The order ID

     * @param {number} itemId - The item ID

     */

    function markItemReady(orderId, itemId) {

        const itemKey = `${orderId}_${itemId}`;

        const state = itemStates[itemKey];



        if (!state) return;



        // Stop timer (prep is complete)

        if (timers[itemKey]) {

            clearInterval(timers[itemKey]);

            delete timers[itemKey];

        }



        // Start waiting timer (prep is complete, now waiting to serve)
        state.status = 'ready';
        state.readyStart = Date.now();
        state.timer = 0;
        state.timerStart = null;

        // Restart timer in ready state for negative counts
        startItemTimer(orderId, itemId, state);



        // Update UI

        updateItemInOrder(orderId, itemId);

        updateOrderHeaderColor(orderId);

        checkAndShowServeButton(orderId);



        // Update floating bar if this is active item

        if (activeItem && activeItem.id === itemId) {

            showFloatingBar(orderId, activeItem, state);

        }



        saveStatesToStorage();

        saveActiveSelection();

    }



    /**

     * Marks item as served/completed (transition from ready to done)

     * 

     * Business Logic:

     * - Changes status from 'ready' to 'done'

     * - Updates icon to checkmark

     * - Updates border color (green maintained)

     * - Hides floating bar

     * - Checks if order can be completed

     * 

     * @param {number} orderId - The order ID

     * @param {number} itemId - The item ID

     */

    function markItemServed(orderId, itemId) {

        const itemKey = `${orderId}_${itemId}`;

        const state = itemStates[itemKey];



        if (!state) return;



        // Update state: ready → done

        state.status = 'done';

        state.timer = null;

        state.timerStart = null;



        // Update UI

        updateItemInOrder(orderId, itemId);

        updateOrderHeaderColor(orderId);

        checkAndShowServeButton(orderId);



        // Hide floating bar

        hideFloatingBar();



        saveStatesToStorage();

        saveActiveSelection();

    }



    /**

     * Cancels an item (marks as cancelled but keeps visible)

     * 

     * Business Logic:

     * - Marks item as cancelled in state

     * - Stops any active timer

     * - Item remains visible in KDS display (with white border)

     * - Item remains in order/cart in database (NOT deleted from order)

     * - Cancelled items remain clickable so they can be prepared again

     * 

     * Important: 

     * - Item is NOT deleted from the actual order/cart

     * - Item remains visible in KDS with red border to indicate cancelled status

     * - Item is excluded from order status calculations (header color, serve button)

     * 

     * @param {number} orderId - The order ID

     * @param {number} itemId - The item ID to cancel

     */

    function cancelItem(orderId, itemId) {

        const itemKey = `${orderId}_${itemId}`;

        const state = itemStates[itemKey];



        // Confirm cancellation

        if (!confirm('Are you sure you want to remove this item from KDS display?')) {

            return;

        }



        // Stop timer if running

        if (timers[itemKey]) {

            clearInterval(timers[itemKey]);

            delete timers[itemKey];

        }



        // Mark item as cancelled in state (don't delete state, mark as cancelled)

        // Why: Keep state so we know item was cancelled, but hide from display

        if (state) {

            state.status = 'cancelled';

            state.timer = null;

            state.timerStart = null;

        } else {

            // If state doesn't exist, create it with cancelled status

            itemStates[itemKey] = {

                status: 'cancelled',

                timer: null,

                timerStart: null,

                prepTime: 300

            };

        }



        // Update item display (keep visible, just mark as cancelled)

        const itemDiv = document.querySelector(`[data-item-key="${itemKey}"]`);

        if (itemDiv) {

            // Update visual state (white border, no red)

            // Item remains visible but marked as cancelled

            updateItemVisualState(itemDiv, state);



            // Update icon (no icon for cancelled items)
            const itemAction = itemDiv.querySelector('.item-action');
            if (itemAction) {
                itemAction.innerHTML = ''; // Clear icon
            }
            // Remove timer display if exists - REMOVED: No longer needed
            /*
            const timerDisplay = itemDiv.querySelector('.item-timer');
            if (timerDisplay) {
                timerDisplay.remove();
            }
            */

            // Remove active class if it was selected
            itemDiv.classList.remove('active');
        }

        // Update order header color (cancelled items are excluded from calculations)
        updateOrderHeaderColor(orderId);

        checkAndShowServeButton(orderId);

        // Order remains visible even if all items are cancelled

        // Note: Order still exists in database, items are just marked as cancelled



        // Hide floating bar if this was the active item

        if (activeItem && activeItem.id === itemId) {

            hideFloatingBar();

        }



        // Save updated states (cancelled status is saved)

        saveStatesToStorage();

        saveActiveSelection();



        // Note: We do NOT call backend API to delete item from order

        // The item remains in the order/cart in the database

        // It's only removed from KDS display view

    }



    function updateItemInOrder(orderId, itemId) {

        const itemKey = `${orderId}_${itemId}`;

        const state = itemStates[itemKey];



        // Use specific selector to target ONLY this item

        const itemDiv = document.querySelector(`[data-item-key="${itemKey}"]`);



        if (!itemDiv) {

            return;

        }



        if (!state) {

            return;

        }



        // Update visual state - ONLY this item

        updateItemVisualState(itemDiv, state);



        // Update icon - ONLY this item

        const itemAction = itemDiv.querySelector('.item-action');

        if (itemAction) {

            itemAction.innerHTML = '';

            const icon = getItemIcon(state);

            if (icon) {

                const img = document.createElement('img');

                img.src = APP.baseUrl + icon;

                img.alt = state.status;

                itemAction.appendChild(img);

            } else {

                // No icon for not_started state

                itemAction.innerHTML = '';

            }

        }



        // Update timer display - ONLY this item
        // REMOVED: Timer display is no longer needed
        /*
        let timerDisplay = itemDiv.querySelector('.item-timer');

        if (state.status === 'prep' && state.timer !== null) {

            if (!timerDisplay) {

                timerDisplay = el('div', 'item-timer', formatTimer(state.timer));

                itemDiv.appendChild(timerDisplay);

            } else {

                timerDisplay.textContent = formatTimer(state.timer);

            }

        } else if (timerDisplay) {

            timerDisplay.remove();

        }
        */

    }



    function updateOrderHeaderColor(orderId) {

        const ticket = document.querySelector(`[data-order-id="${orderId}"]`);

        if (!ticket) return;



        const headerTop = ticket.querySelector('.order-header-top');

        if (!headerTop) return;



        // Get all items for this order

        const itemDivs = ticket.querySelectorAll('.order-item');

        const items = Array.from(itemDivs).map(div => ({

            id: div.getAttribute('data-item-id')

        }));



        const color = getOrderHeaderColor(orderId, items);

        headerTop.className = `order-header-top ${color}`;

    }



    /**

     * Determines if "Serve & Complete" button should be shown

     * 

     * Business Logic:

     * - Button shows when ALL active items are either "ready" or "done"

     * - Items can be mixed: some ready, some done - button still shows

     * - Items in "prep" or "not_started" prevent button from showing

     * - Cancelled/removed items are ignored

     * 

     * Why this logic:

     * - Allows completing order even if some items are ready (not yet served)

     * - Kitchen staff can mark order complete when all items are ready to serve

     * - Provides flexibility in workflow

     * 

     * @param {number} orderId - The order ID

     * @param {Array} items - Array of item objects for this order

     * @returns {boolean} True if button should be shown

     */

    function shouldShowServeButton(orderId, items) {

        if (!items || items.length === 0) return false;



        let allReadyOrDone = true;  // Changed from allDone to allReadyOrDone

        let hasActiveItems = false;



        items.forEach(item => {

            const key = `${orderId}_${item.id}`;

            const state = itemStates[key];



            // Skip items that don't exist in state (removed items)

            if (!state) {

                return; // Item was removed, skip it

            }



            // Skip cancelled items

            if (state.status === 'cancelled') {

                return;

            }



            hasActiveItems = true;



            // Check if item is ready OR done (both are acceptable)

            // If item is prep or not_started, order is not ready to complete

            if (state.status !== 'done' && state.status !== 'ready') {

                allReadyOrDone = false;

            }

        });



        return allReadyOrDone && hasActiveItems;

    }



    function checkAndShowServeButton(orderId) {

        const ticket = document.querySelector(`[data-order-id="${orderId}"]`);

        if (!ticket) return;



        // Get all items for this order

        const itemDivs = ticket.querySelectorAll('.order-item');

        const items = Array.from(itemDivs).map(div => ({

            id: div.getAttribute('data-item-id')

        }));



        // Remove existing serve button

        const existingBtn = ticket.querySelector('.serve-button-container');

        if (existingBtn) {

            existingBtn.remove();

        }



        // Get order type from ticket

        const orderType = ticket.getAttribute('data-order-type') || 'sale';



        // Add serve button if all items are ready or done (mixed states allowed)

        if (shouldShowServeButton(orderId, items)) {

            ticket.appendChild(createServeButton(orderId, orderType));

        }

    }



    function createServeButton(orderId, orderType) {

        const container = el('div', 'serve-button-container');

        const button = el('button', 'serve-button', 'Serve & Complete');



        button.addEventListener('click', function () {

            completeOrder(orderId, orderType);

        });



        container.appendChild(button);

        return container;

    }



    function completeOrder(orderId, orderType) {

        // Get order type from ticket if not provided

        const ticket = document.querySelector(`[data-order-id="${orderId}"]`);

        if (!orderType && ticket) {

            orderType = ticket.getAttribute('data-order-type') || 'sale';

        }

        orderType = orderType || 'sale';



        // Show loading state

        if (ticket) {

            const serveButton = ticket.querySelector('.serve-button');

            if (serveButton) {

                serveButton.disabled = true;

                serveButton.textContent = 'Completing...';

            }

        }



        // Send API call to backend to mark order as completed

        $.ajax({

            url: APP.baseUrl + 'Production_Unit/complete_kds_order',

            type: 'POST',

            data: {

                order_id: orderId,

                order_type: orderType,

                [APP.csrfTokenName]: APP.csrfTokenHash

            },

            dataType: 'json',

            success: function (response) {

                // Update CSRF token if provided in response

                if (response.csrf_token) {

                    APP.csrfTokenHash = response.csrf_token;

                }



                if (response.error) {

                    alert('Error: ' + response.message);

                    // Re-enable button on error

                    if (ticket) {

                        const serveButton = ticket.querySelector('.serve-button');

                        if (serveButton) {

                            serveButton.disabled = false;

                            serveButton.textContent = 'Serve & Complete';

                        }

                    }

                    return;

                }



                // Remove order from display on success

                if (ticket) {

                    ticket.style.transition = 'opacity 0.3s';

                    ticket.style.opacity = '0';

                    setTimeout(() => {

                        ticket.remove();

                    }, 300);

                }



                // Clean up timers for this order

                Object.keys(timers).forEach(key => {

                    if (key.startsWith(`${orderId}_`)) {

                        clearInterval(timers[key]);

                        delete timers[key];

                    }

                });



                // Clean up states for this order

                Object.keys(itemStates).forEach(key => {

                    if (key.startsWith(`${orderId}_`)) {

                        delete itemStates[key];

                    }

                });



                // Remove from localStorage

                saveStatesToStorage();



                // Clear active selection if this was the active order

                if (activeOrderId === orderId) {

                    hideFloatingBar();

                } else {

                    saveActiveSelection();

                }

            },

            error: function (xhr) {

                console.error('Failed to complete order:', xhr.responseText);

                alert('Failed to complete order. Please try again.');

                // Re-enable button on error

                if (ticket) {

                    const serveButton = ticket.querySelector('.serve-button');

                    if (serveButton) {

                        serveButton.disabled = false;

                        serveButton.textContent = 'Serve & Complete';

                    }

                }

            }

        });

    }



    /* ================= RESET FUNCTIONALITY ================= */



    function resetAllData() {

        // Confirm with user
        customConfirm(
            'Are you sure you want to reset all KDS data? This will clear all item states, timers, and selections.',
            function() {
                // User confirmed - proceed with reset
                proceedWithReset();
            },
            function() {
                // User cancelled - do nothing
                return;
            }
        );

        function proceedWithReset() {



        // Clear all timers

        Object.keys(timers).forEach(key => {

            clearInterval(timers[key]);

        });

        timers = {};



        // Clear all item states

        itemStates = {};



        // Clear active selection

        activeItem = null;

        activeItemDiv = null;

        activeOrderId = null;



        // Clear localStorage

        try {

            localStorage.removeItem(STORAGE_KEY);

            localStorage.removeItem(ACTIVE_SELECTION_KEY);

            localStorage.removeItem(CUSTOM_TIMER_KEY);

        } catch (e) {

            console.warn('Failed to clear localStorage:', e);

        }



        // Hide floating bar

        hideFloatingBar();



        // Reload orders to reflect reset state

        fetchKDSData();



        // Show confirmation
        customAlert('All KDS data has been reset successfully!');

        // Full page refresh to restore original state
        setTimeout(() => {
            window.location.reload();
        }, 1000);
        } // End of proceedWithReset function
    }



    // Add reset button event listener (using event delegation for reliability)

    $(document).on('click', '#reset-btn', function () {

        resetAllData();

    });



    /* ================= CUSTOM TIMER EVENT LISTENERS ================= */



    // Initial timer button click - show set timer modal

    $(document).on('click', '#initial-timer-button', function () {

        showCustomTimerModal();

    });



    // Timer display click - show set timer modal (for expanded display)

    $(document).on('click', '#custom-timer-display', function () {

        showCustomTimerModal();

    });



    // Add time button click

    $(document).on('click', '#timer-add-btn', function () {

        showAddTimeModal();

    });



    // Cancel timer button click

    $(document).on('click', '#timer-cancel-btn', function () {

        resetCustomTimer();

    });



    // Initialize custom timer display

    updateCustomTimerDisplay();



    /* ================= MASONRY LAYOUT ================= */

    /**
     * Masonry Layout Engine
     * Dynamically positions order cards to fill all vertical gaps
     * Creates a zig-zag, space-efficient layout
     */

    let masonryConfig = {
        columns: 4,
        gap: 16,
        cardWidth: 0,
        containerWidth: 0
    };

    /**
     * Calculates and applies masonry layout to all order cards
     * This creates the gap-filling behavior where short cards flow up
     */
    function applyMasonryLayout() {
        const container = document.querySelector('.orders-container');
        if (!container) return;

        // Ensure we have an inner wrapper for proper scrolling
        let wrapper = container.querySelector('.masonry-wrapper');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'masonry-wrapper';
            
            const existingCards = Array.from(container.querySelectorAll('.order-ticket'));
            existingCards.forEach(card => wrapper.appendChild(card));
            container.appendChild(wrapper);
        }

        // Get cards in DOM order (chronological)
        const cards = Array.from(wrapper.querySelectorAll('.order-ticket:not([style*="display: none"])'));
        if (cards.length === 0) {
            wrapper.style.height = '0px';
            container.style.height = 'calc(100vh - 182px)';
            return;
        }

        // Chronological layout with space efficiency - maintains visual order
        const columns = 4;
        const gap = 8;
        const containerPadding = 20;
        const containerWidth = container.offsetWidth - (containerPadding * 2);
        const cardWidth = (containerWidth - (gap * (columns - 1))) / columns;
        
        // Track the bottom position of each column
        const columnHeights = new Array(columns).fill(0);
        
        // Position cards in chronological order but maintain visual flow
        cards.forEach((card, index) => {
            // Try to place in the next available column to maintain visual order
            let targetColumn = index % columns;
            
            // But if that column is much taller than others, use the shortest
            const maxHeight = Math.max(...columnHeights);
            const targetHeight = columnHeights[targetColumn];
            
            if (targetHeight > maxHeight + 100) { // If target column is much taller
                // Use the shortest column instead
                targetColumn = columnHeights.indexOf(Math.min(...columnHeights));
            }
            
            // Calculate position
            const left = targetColumn * (cardWidth + gap);
            const top = columnHeights[targetColumn];
            
            // Apply position and size
            card.style.position = 'absolute';
            card.style.left = `${left}px`;
            card.style.top = `${top}px`;
            card.style.width = `${cardWidth}px`;
            
            // Update column height (add card height + gap)
            const cardHeight = card.offsetHeight;
            columnHeights[targetColumn] += cardHeight + gap;
        });

        // Calculate actual total height based on final positions
        const finalPositions = Array.from(cards).map(card => ({
            top: parseInt(card.style.top) || 0,
            height: card.offsetHeight
        }));
        
        const totalHeight = finalPositions.length > 0 
            ? Math.max(...finalPositions.map(pos => pos.top + pos.height)) + gap
            : 0;

        // Set wrapper height
        wrapper.style.position = 'relative';
        wrapper.style.width = '100%';
        wrapper.style.height = `${totalHeight}px`;
        wrapper.style.minHeight = '100%';

        // Container maintains fixed viewport height with scrolling
        container.style.height = 'calc(100vh - 182px)';
        container.style.maxHeight = 'calc(100vh - 182px)';
        container.style.overflowY = 'auto';
        container.style.overflowX = 'hidden';
    }

    /**
     * Debounced masonry layout to prevent excessive recalculations
     */
    let masonryTimeout = null;
    function scheduleMasonryLayout() {
        if (masonryTimeout) clearTimeout(masonryTimeout);
        masonryTimeout = setTimeout(() => {
            applyMasonryLayout();
        }, 200);

        // Fix existing cancelled items to make them clickable (hotfix for existing cancelled items)
        fixCancelledItemsClickability();
    }

    /**
     * Fixes existing cancelled items to make them clickable
     * This is a hotfix to handle items that were cancelled before the click fix was applied
     */
    function fixCancelledItemsClickability() {
        document.querySelectorAll('.order-item').forEach(itemDiv => {
            const itemKey = itemDiv.getAttribute('data-item-key');
            if (itemKey && itemStates[itemKey] && itemStates[itemKey].status === 'cancelled') {
                // Check if item already has click listener by testing if it's clickable
                const hasClickListener = itemDiv.onclick !== null ||
                    itemDiv.addEventListener && itemDiv.hasAttribute('data-click-fixed');

                if (!hasClickListener) {
                    // Add click event listener for cancelled items
                    itemDiv.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const orderId = itemDiv.getAttribute('data-order-id');
                        const itemId = itemDiv.getAttribute('data-item-id');
                        const item = { id: itemId };
                        selectItem(orderId, item, itemDiv);
                    });

                    // Mark as fixed to avoid duplicate listeners
                    itemDiv.setAttribute('data-click-fixed', 'true');
                }
            }
        });
    }

    /**
     * Initialize masonry layout on window resize
     */
    $(window).on('resize', function () {
        scheduleMasonryLayout();
    });

    /**
     * Observe DOM changes to re-layout when cards are added/removed/modified
     */
    function initMasonryObserver() {
        const container = document.querySelector('.orders-container');
        if (!container) return;

        const observer = new MutationObserver(function (mutations) {
            // Check if any mutations affected card visibility or content
            const shouldReLayout = mutations.some(mutation => {
                return mutation.type === 'childList' ||
                    mutation.type === 'attributes' ||
                    (mutation.type === 'characterData');
            });

            if (shouldReLayout) {
                scheduleMasonryLayout();
            }
        });

        observer.observe(container, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }

    // Initialize masonry observer
    initMasonryObserver();


    /* ================= HELPER ================= */



    function el(tag, className = null, text = null) {

        const element = document.createElement(tag);

        if (className) element.className = className;

        if (text !== null) element.textContent = text;

        return element;

    }



});

