<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookedSeatsByRoute = @json($bookedSeats);
    const trainRouteSelect = document.getElementById('train_route_id');
    const carSelect = document.getElementById('carSelect');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    const totalPriceEl = document.getElementById('totalPrice');
    const basePriceEl = document.getElementById('basePrice');
    const servicesPriceEl = document.getElementById('servicesPrice');
    const seatMap = document.getElementById('seatMap');
    const selectedCar = document.getElementById('selectedCar');
    const selectedSeat = document.getElementById('selectedSeat');

    let currentRouteData = null;

    function bookedForCar(bookedSeats, car) {
        return bookedSeats.filter(seat => Number(seat.car) === Number(car));
    }

    function calculateTotal() {
        let basePrice = 0;
        let servicesPrice = 0;

        const selectedOption = trainRouteSelect.options[trainRouteSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.basePrice) {
            basePrice = parseFloat(selectedOption.dataset.basePrice) || 0;
        }

        serviceCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                servicesPrice += parseFloat(checkbox.dataset.price) || 0;
            }
        });

        const total = basePrice + servicesPrice;

        totalPriceEl.textContent = total.toFixed(2);
        basePriceEl.textContent = basePrice.toFixed(2);
        servicesPriceEl.textContent = servicesPrice.toFixed(2);
    }

    function populateCarSelect() {
        const selectedOption = trainRouteSelect.options[trainRouteSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            carSelect.innerHTML = '<option value="">-- Оберіть вагон --</option>';
            carSelect.disabled = true;
            seatMap.innerHTML = `
                <p class="text-muted text-center mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Оберіть рейс для відображення вагонів
                </p>
            `;
            currentRouteData = null;
            return;
        }

        const carCount = parseInt(selectedOption.dataset.carCount, 10) || 10;
        const seatsPerCar = parseInt(selectedOption.dataset.seatsPerCar, 10) || 54;
        const bookedSeats = bookedSeatsByRoute[selectedOption.value] || [];

        currentRouteData = {
            seatsPerCar,
            bookedSeats,
            carType: selectedOption.dataset.carType || 'platskart'
        };

        carSelect.innerHTML = '<option value="">-- Оберіть вагон --</option>';
        for (let car = 1; car <= carCount; car++) {
            const bookedInCar = bookedForCar(bookedSeats, car).length;
            const availableInCar = seatsPerCar - bookedInCar;
            carSelect.innerHTML += `
                <option value="${car}">
                    Вагон №${car} (${availableInCar}/${seatsPerCar} вільно)
                </option>
            `;
        }
        carSelect.disabled = false;

        if (selectedCar.value) {
            carSelect.value = selectedCar.value;
            if (carSelect.value) {
                renderSeatMap();
                return;
            }
        }

        seatMap.innerHTML = `
            <p class="text-muted text-center mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Оберіть вагон для відображення місць
            </p>
        `;
    }

    function renderSeatMap() {
        const car = parseInt(carSelect.value, 10);

        if (!car || !currentRouteData) {
            seatMap.innerHTML = `
                <p class="text-muted text-center mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Оберіть вагон для відображення місць
                </p>
            `;
            return;
        }

        const seatsPerCar = currentRouteData.seatsPerCar;
        const bookedInCar = bookedForCar(currentRouteData.bookedSeats, car);
        const availableInCar = seatsPerCar - bookedInCar.length;

        let html = `
            <div class="car-info">
                <h5 class="mb-1"><i class="bi bi-train me-1"></i>Вагон №${car}</h5>
                <small class="text-muted">
                    Всього: ${seatsPerCar} | Зайнято: ${bookedInCar.length} |
                    <span class="text-success fw-bold">Вільно: ${availableInCar}</span>
                </small>
            </div>
            <div class="text-center mb-3">
                <span class="badge ${currentRouteData.carType === 'coupe' ? 'bg-primary' : 'bg-warning text-dark'}">
                    ${currentRouteData.carType === 'coupe' ? 'Купе' : 'Плацкарт'}
                </span>
            </div>
            <div class="seats-grid ${currentRouteData.carType === 'coupe' ? 'coupe' : ''}">
        `;

        for (let seat = 1; seat <= seatsPerCar; seat++) {
            const isBooked = bookedInCar.some(item => Number(item.seat) === seat);
            const isSelected = Number(selectedCar.value) === car && Number(selectedSeat.value) === seat;
            const seatClass = isBooked ? 'seat booked' : (isSelected ? 'seat selected' : 'seat available');

            html += `
                <div class="${seatClass}"
                     data-car="${car}"
                     data-seat="${seat}"
                     data-booked="${isBooked}">
                    ${seat}
                </div>
            `;
        }

        html += `
            </div>
            <div class="seat-legend">
                <div class="legend-item">
                    <div class="legend-box" style="background: #d4edda; border-color: #28a745;"></div>
                    <span>Вільно</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box" style="background: #f8d7da; border-color: #dc3545;"></div>
                    <span>Зайнято</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box" style="background: #007bff; border-color: #0056b3;"></div>
                    <span>Обрано</span>
                </div>
            </div>
        `;

        seatMap.innerHTML = html;

        seatMap.querySelectorAll('.seat').forEach(el => {
            el.addEventListener('click', function() {
                selectSeat(
                    Number(this.dataset.car),
                    Number(this.dataset.seat),
                    this.dataset.booked === 'true'
                );
            });
        });
    }

    function selectSeat(car, seat, isBooked) {
        if (isBooked) {
            return;
        }

        selectedCar.value = car;
        selectedSeat.value = seat;

        document.querySelectorAll('.seat').forEach(el => {
            el.classList.remove('selected');
            if (Number(el.dataset.car) === car && Number(el.dataset.seat) === seat && el.dataset.booked !== 'true') {
                el.classList.add('selected');
            }
        });
    }

    trainRouteSelect.addEventListener('change', function() {
        calculateTotal();
        selectedCar.value = '';
        selectedSeat.value = '';
        carSelect.value = '';
        populateCarSelect();
    });

    carSelect.addEventListener('change', function() {
        selectedCar.value = carSelect.value;
        selectedSeat.value = '';
        renderSeatMap();
    });

    serviceCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateTotal);
    });

    calculateTotal();
    populateCarSelect();
});
</script>
