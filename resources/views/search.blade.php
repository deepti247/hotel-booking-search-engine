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

<!-- Search Box -->
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

<!-- Results -->
<div id="results" class="mt-4"></div>

<script>
let guests = 1;

// Guest counter
function changeGuest(val) {
    guests += val;

    if (guests < 1) guests = 1;
    if (guests > 3) guests = 3;

    document.getElementById('guest_count').value = guests;
}

// Validate dates
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

// GET API call
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

// Render results
function renderResults(rooms) {

    let html = "";

    if (!rooms.length) {
        html = `<div class="alert alert-warning">No rooms found</div>`;
    }

    rooms.forEach(room => {

        html += `
        <div class="card room-card shadow-sm">
            <div class="row g-0">

                <div class="col-md-4">
                    
                    <img src="${getRoomImage(room.room_type)}" class="img-fluid h-100">
                </div>

                <div class="col-md-8 p-3">
                    <h5>${room.room_type}</h5>
                    <p>Status: <strong>${room.status}</strong></p>

                    ${room.options.map(opt => `
                        <div class="border-top pt-2 mt-2">
                            <strong>${opt.type.replace('_',' ')}</strong><br>

                            <span class="old-price">₹${opt.original_total ?? '-'}</span><br>
                            <span class="price text-success">₹${opt.final_price ?? '-'}</span>
                            <span class="text-muted"> (${opt.discount_percent}% off)</span>

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
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    let today = new Date().toISOString().split('T')[0];

    // Disable past dates
    document.getElementById("check_in").setAttribute("min", today);
    document.getElementById("check_out").setAttribute("min", today);

});
</script>
<script>
function getRoomImage(roomType) {

    roomType = roomType.toLowerCase();

    if (roomType.includes('deluxe')) {
        return "{{ asset('images/deluxe.jpg') }}";
    }

    if (roomType.includes('standard')) {
        return "{{ asset('images/standard.jpg') }}";
    }

    // default image
    return "{{ asset('images/default.jpg') }}";
}
</script>
</body>
</html>