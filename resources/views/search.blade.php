<!DOCTYPE html>
<html>
<head>
    <title>Hotel Search</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .search-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }
        .room-card {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .price {
            font-size: 20px;
            font-weight: bold;
        }
        .old-price {
            text-decoration: line-through;
            color: gray;
            font-size: 14px;
        }
    </style>
</head>

<body class="container mt-4">

<h4 class="mb-3">Select your stay</h4>

<!--  SEARCH BOX -->
<div class="search-box row align-items-end">

    <div class="col-md-3">
        <label>Check-in</label>
        <input type="date" id="check_in" class="form-control">
    </div>

    <div class="col-md-3">
        <label>Check-out</label>
        <input type="date" id="check_out" class="form-control">
    </div>

    <div class="col-md-3">
        <label>Guests</label>
        <div class="d-flex">
            <button class="btn btn-outline-secondary" onclick="changeGuest(-1)">-</button>
            <input type="text" id="guest_count" class="form-control text-center mx-2" value="1" readonly>
            <button class="btn btn-outline-secondary" onclick="changeGuest(1)">+</button>
        </div>
    </div>

    <div class="col-md-3">
        <button class="btn btn-dark w-100" onclick="searchRooms()">Search</button>
    </div>

</div>

<!-- RESULTS -->
<div id="results" class="mt-4"></div>

<script>
let guests = 1;

//  Guest counter (now supports 4)
function changeGuest(val) {
    guests += val;

    if (guests < 1) guests = 1;
    if (guests > 4) guests = 4;

    document.getElementById('guest_count').value = guests;
}

//  Date validation
function validateDates(checkIn, checkOut) {
    if (!checkIn || !checkOut) {
        alert("Select both dates");
        return false;
    }

    if (checkIn >= checkOut) {
        alert("Check-out must be after check-in");
        return false;
    }

    return true;
}

//  API call
function searchRooms() {
    let checkIn = document.getElementById('check_in').value;
    let checkOut = document.getElementById('check_out').value;

    if (!validateDates(checkIn, checkOut)) return;

    let url = `{{ url('/api/search') }}?check_in=${checkIn}&check_out=${checkOut}&guests=${guests}`;

    fetch(url)
    .then(res => res.json())
    .then(data => renderResults(data.data))
    .catch(err => console.log(err));
}

//  Render results (UPDATED FOR RATE PLANS)
function renderResults(rooms) {

    let html = "";

    if (!rooms.length) {
        html = `<div class="alert alert-warning">No rooms found</div>`;
    }

    rooms.forEach(room => {

        html += `
        <div class="card room-card shadow-sm">
            <div class="row g-0">

                <!-- IMAGE -->
                <div class="col-md-4">
                    <img src="${getRoomImage(room.room_type)}" class="img-fluid h-100">
                </div>

                <!-- DETAILS -->
                <div class="col-md-8 p-3">
                    <h5 class="text-capitalize">${room.room_type}</h5>
                    <p>Status: <strong>${room.status}</strong></p>
                    <p>Available Rooms: ${room.available_rooms}</p>

                    ${room.rate_plans.length === 0 ? `
                        <div class="text-danger">
                            No rate plans available for selected guests
                        </div>
                    ` : ''}

                    ${room.rate_plans.map(plan => `
                        <div class="border-top pt-2 mt-2">

                            <!-- PLAN -->
                            <strong>
                                ${plan.plan_name} 
                                (${formatMeal(plan.meal_type)})
                            </strong><br>

                            <!-- BASE PRICE -->
                            <small>
                                ₹${plan.price_per_night} × ${room.nights} nights
                                = ₹${plan.original_total}
                            </small><br>

                            <!-- DISCOUNT -->
                            <span class="text-muted">
                                Discount: ${plan.discount_percent}%
                            </span><br>

                            <!-- FINAL -->
                            <span class="price text-success">
                                ₹${plan.final_price}
                            </span>

                            <div class="mt-2">
                                <button class="btn btn-sm btn-dark">Select</button>
                            </div>

                        </div>
                    `).join('')}

                </div>

            </div>
        </div>
        `;
    });

    document.getElementById('results').innerHTML = html;
}

//  Meal label formatter
function formatMeal(mealType) {
    if (mealType === 'none') return 'Room Only';
    if (mealType === 'breakfast') return 'Breakfast Included';
    if (mealType === 'all_meals') return 'All Meals Included';
    return mealType;
}

//  Disable past dates
document.addEventListener("DOMContentLoaded", function () {
    let today = new Date().toISOString().split('T')[0];

    document.getElementById("check_in").setAttribute("min", today);
    document.getElementById("check_out").setAttribute("min", today);
});

//  Room images
function getRoomImage(roomType) {

    roomType = roomType.toLowerCase();

    if (roomType.includes('deluxe')) {
        return "{{ asset('images/deluxe.jpg') }}";
    }

    if (roomType.includes('standard')) {
        return "{{ asset('images/standard.jpg') }}";
    }

    return "{{ asset('images/default.jpg') }}";
}
</script>

</body>
</html>
