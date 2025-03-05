class Calendar {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.displayElementId = options.displayElementId || 'appointmentDisplay';
        this.displayElement = document.getElementById(this.displayElementId);
        
        this.options = {
            minDate: options.minDate || new Date(),
            maxDate: options.maxDate || new Date(new Date().setFullYear(new Date().getFullYear() + 1)),
            timeSlotInterval: options.timeSlotInterval || 30,
            minTime: options.minTime || '09:00',
            maxTime: options.maxTime || '17:00',
            onChange: options.onChange || (() => {})
        };
        
        this.selectedDate = null;
        this.selectedTime = null;
        this.currentMonth = new Date();
        this.bookedSlots = []; // Pour stocker les créneaux déjà réservés
        
        this.init();
    }
    
    init() {
        this.createCalendarElement();
        this.attachEventListeners();
        this.fetchBookedSlots(); // Récupérer les créneaux déjà réservés
    }
    
    // Nouvelle méthode pour récupérer les créneaux réservés
    fetchBookedSlots() {
        fetch('php/get_booked_slots.php')
            .then(response => response.json())
            .then(data => {
                if (data.bookedSlots) {
                    this.bookedSlots = data.bookedSlots;
                    this.render(); // Recharger le calendrier avec les créneaux désactivés
                }
            })
            .catch(error => {
                console.error('Error fetching booked slots:', error);
                this.render(); // Rendre quand même le calendrier en cas d'erreur
            });
    }
    
    createCalendarElement() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'calendar-wrapper';
        
        // Create popup
        this.popup = document.createElement('div');
        this.popup.className = 'calendar-popup';
        this.popup.style.display = 'none'; // Start hidden
        
        // Insert calendar after the display input
        this.displayElement.parentNode.insertBefore(this.wrapper, this.displayElement.nextSibling);
        this.wrapper.appendChild(this.popup);
    }
    
    attachEventListeners() {
        // Toggle calendar on input click
        this.displayElement.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleCalendar();
        });
        
        // Close calendar when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target) && e.target !== this.displayElement) {
                this.hideCalendar();
            }
        });
    }
    
    toggleCalendar() {
        if (this.popup.style.display === 'block') {
            this.hideCalendar();
        } else {
            this.showCalendar();
        }
    }
    
    showCalendar() {
        // Position the calendar popup correctly
        const inputRect = this.displayElement.getBoundingClientRect();
        this.popup.style.top = `${inputRect.height + 5}px`;
        this.popup.style.display = 'block';
        this.render();
    }
    
    hideCalendar() {
        this.popup.style.display = 'none';
    }
    
    render() {
        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth();
        
        this.popup.innerHTML = `
            <div class="calendar-header">
                <span class="calendar-title">${new Date(year, month).toLocaleString('default', { month: 'long', year: 'numeric' })}</span>
                <div class="calendar-nav">
                    <button type="button" class="prev-month">&lt;</button>
                    <button type="button" class="next-month">&gt;</button>
                </div>
            </div>
            <div class="calendar-grid">
                ${this.renderWeekdays()}
                ${this.renderDays()}
            </div>
            ${this.renderTimePicker()}
        `;
        
        this.attachCalendarHandlers();
    }
    
    renderWeekdays() {
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return weekdays.map(day => `
            <div class="calendar-weekday">${day}</div>
        `).join('');
    }
    
    renderDays() {
        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth();
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();
        
        let days = [];
        
        // Previous month days
        const prevMonthDays = new Date(year, month, 0).getDate();
        for (let i = startingDay - 1; i >= 0; i--) {
            days.push(`<div class="calendar-day other-month">${prevMonthDays - i}</div>`);
        }
        
        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isToday = this.isToday(date);
            const isSelected = this.isSelected(date);
            const isDisabled = this.isDisabled(date);
            
            // Création d'un attribut data-date personnalisé avec le format YYYY-MM-DD
            const dateAttr = this.formatLocalDate(date);
            
            days.push(`
                <div class="calendar-day ${isToday ? 'today' : ''} ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}"
                     data-date="${dateAttr}"
                     ${isDisabled ? 'disabled' : ''}>
                    ${day}
                </div>
            `);
        }
        
        // Next month days
        const remainingDays = 42 - days.length;
        for (let day = 1; day <= remainingDays; day++) {
            days.push(`<div class="calendar-day other-month">${day}</div>`);
        }
        
        return days.join('');
    }
    
    // Format une date en YYYY-MM-DD sans utiliser toISOString()
    formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    renderTimePicker() {
        if (!this.selectedDate) return '';
        
        const timeSlots = this.generateTimeSlots();
        
        return `
            <div class="time-picker">
                <div class="time-picker-header">Select Time</div>
                <div class="time-slots">
                    ${timeSlots.map(time => {
                        // Vérifier si ce créneau est déjà réservé
                        const isBooked = this.isTimeSlotBooked(this.selectedDate, time);
                        return `
                            <div class="time-slot ${this.selectedTime === time ? 'selected' : ''} ${isBooked ? 'disabled' : ''}"
                                 data-time="${time}"
                                 ${isBooked ? 'disabled' : ''}>
                                ${this.formatTime(time)}
                                ${isBooked ? '<span class="booked-indicator">Réservé</span>' : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }
    
    // Nouvelle méthode pour vérifier si un créneau est déjà réservé
    isTimeSlotBooked(date, time) {
        if (!date || !this.bookedSlots.length) return false;
        
        const dateStr = this.formatLocalDate(date);
        
        return this.bookedSlots.some(slot => {
            return slot.appointment_date === dateStr && slot.appointment_time === time;
        });
    }
    
    generateTimeSlots() {
        const slots = [];
        const [startHour, startMinute] = this.options.minTime.split(':').map(Number);
        const [endHour, endMinute] = this.options.maxTime.split(':').map(Number);
        
        let currentHour = startHour;
        let currentMinute = startMinute;
        
        while (currentHour < endHour || (currentHour === endHour && currentMinute <= endMinute)) {
            slots.push(`${currentHour.toString().padStart(2, '0')}:${currentMinute.toString().padStart(2, '0')}`);
            
            currentMinute += this.options.timeSlotInterval;
            if (currentMinute >= 60) {
                currentHour++;
                currentMinute = 0;
            }
        }
        
        return slots;
    }
    
    formatTime(time) {
        const [hours, minutes] = time.split(':').map(Number);
        return new Date(2000, 0, 1, hours, minutes).toLocaleTimeString('default', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
    
    attachCalendarHandlers() {
        // Navigation
        this.popup.querySelector('.prev-month').addEventListener('click', (event) => {
            event.stopPropagation(); // Empêche la fermeture du calendrier
            event.preventDefault();
            this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() - 1);
            this.render();
        });
        
        this.popup.querySelector('.next-month').addEventListener('click', (event) => {
            event.stopPropagation(); // Empêche la fermeture du calendrier
            event.preventDefault();
            this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1);
            this.render();
        });
        
        // Day selection
        this.popup.querySelectorAll('.calendar-day:not(.other-month):not(.disabled)').forEach(day => {
            day.addEventListener('click', (event) => {
                event.stopPropagation(); // Prevent closing the calendar
        
                const dateStr = day.dataset.date;
                const [year, month, dayNumber] = dateStr.split('-').map(Number);
                
                // Créons la date en préservant le jour exact
                this.selectedDate = new Date(year, month - 1, dayNumber);
                
                // Vérifions que le jour est correctement défini (pour éviter le problème de changement de jour)
                if (this.selectedDate.getDate() !== dayNumber) {
                    // Ajustement si nécessaire
                    this.selectedDate = new Date(year, month - 1, dayNumber, 12, 0, 0);
                }
                
                // Remove selected class from all days
                this.popup.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                // Add selected class to clicked day
                day.classList.add('selected');
        
                // Instead of full re-render, only update time picker
                this.popup.querySelector('.time-picker')?.remove();
                this.popup.insertAdjacentHTML('beforeend', this.renderTimePicker());
                
                // Attacher les gestionnaires d'événements pour le time picker
                this.attachTimeHandlers();
            });
        });
        
        // Time selection
        this.attachTimeHandlers();
    }

    attachTimeHandlers() {
        const timeSlots = this.popup.querySelectorAll('.time-slot:not(.disabled)');
        if (timeSlots && timeSlots.length > 0) {
            timeSlots.forEach(slot => {
                slot.addEventListener('click', (event) => {
                    event.stopPropagation(); // Empêche la fermeture du calendrier
                    // Supprimer la classe selected de tous les créneaux
                    this.popup.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                    // Ajouter la classe selected au créneau cliqué
                    slot.classList.add('selected');
                    
                    this.selectedTime = slot.dataset.time;
                    this.updateInput();
                    this.hideCalendar();
                    this.options.onChange(this.getSelection());
                });
            });
        }
    }
    
    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }
    
    isSelected(date) {
        return this.selectedDate && date.toDateString() === this.selectedDate.toDateString();
    }
    
    isDisabled(date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date < today || date > this.options.maxDate;
    }
    
    updateInput() {
        if (this.selectedDate && this.selectedTime) {
            // Afficher la date formatée pour l'utilisateur
            const formattedDate = this.selectedDate.toLocaleDateString('default', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            this.displayElement.value = `${formattedDate} at ${this.formatTime(this.selectedTime)}`;
            
            // SOLUTION: Créer manuellement un format de date sans problème de fuseau horaire
            const [hours, minutes] = this.selectedTime.split(':').map(Number);
            
            // Créer une nouvelle date avec les mêmes composants et l'heure sélectionnée
            // Nous utilisons l'année, le mois et le jour exacts sélectionnés
            const year = this.selectedDate.getFullYear();
            const month = this.selectedDate.getMonth(); // 0-11 en JavaScript
            const day = this.selectedDate.getDate();
            
            // 1. Créer une nouvelle date avec les composants exacts, pour éviter les problèmes
            const dateTime = new Date(year, month, day, hours, minutes, 0, 0);
            
            // 2. Formater la date en YYYY-MM-DD sans utiliser toISOString()
            const formattedYear = dateTime.getFullYear();
            const formattedMonth = String(dateTime.getMonth() + 1).padStart(2, '0');
            const formattedDay = String(dateTime.getDate()).padStart(2, '0');
            const formattedHours = String(dateTime.getHours()).padStart(2, '0');
            const formattedMinutes = String(dateTime.getMinutes()).padStart(2, '0');
            
            // 3. Créer la chaîne ISO au format YYYY-MM-DDTHH:MM:00
            const isoString = `${formattedYear}-${formattedMonth}-${formattedDay}T${formattedHours}:${formattedMinutes}:00`;
            
            // Mettre à jour la valeur de l'input caché
            this.input.value = isoString;
            
            // Pour déboguer
            console.log('Date sélectionnée:', this.selectedDate.toDateString());
            console.log('Heure sélectionnée:', this.selectedTime);
            console.log('Date formatée pour PHP:', isoString);
        } else {
            this.displayElement.value = '';
            this.input.value = '';
        }
    }
    
    getSelection() {
        if (!this.selectedDate || !this.selectedTime) return null;
        
        // Extraire les heures et minutes
        const [hours, minutes] = this.selectedTime.split(':').map(Number);
        
        // Créer une nouvelle date avec les composants exacts pour éviter les problèmes de fuseau horaire
        const year = this.selectedDate.getFullYear();
        const month = this.selectedDate.getMonth();
        const day = this.selectedDate.getDate();
        
        // Créer une nouvelle date avec ces composants
        return new Date(year, month, day, hours, minutes, 0, 0);
    }
}