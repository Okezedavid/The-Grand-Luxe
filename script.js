// ==========================================
// HOTEL RESERVATION SYSTEM - JAVASCRIPT
// Handles all frontend interactions and localStorage
// ==========================================

// Room Data (Mock Database)
const roomsData = [
  {
    id: 1,
    name: "Deluxe King Suite",
    price: 299,
    description:
      "Spacious suite with king-sized bed, city views, and luxury amenities. Perfect for couples seeking ultimate comfort.",
    image: "assets/imgs/francesca-saraco-_dS27XGgRyQ-unsplash.jpg",
    features: ["King Bed", "City View", "Mini Bar", "WiFi"],
    maxGuests: 2,
  },
  {
    id: 2,
    name: "Executive Ocean View",
    price: 399,
    description:
      "Premium ocean-facing room with private balcony, perfect for romantic getaways and special occasions.",
    image: "assets/imgs/juliana-morales-ramirez-GmW4hfTX0ns-unsplash.jpg",
    features: ["Ocean View", "Balcony", "Jacuzzi", "WiFi"],
    maxGuests: 2,
  },
  {
    id: 3,
    name: "Presidential Suite",
    price: 799,
    description:
      "The ultimate luxury experience with separate living area, dining room, and panoramic city views.",
    image: "assets/imgs/linus-mimietz-p3UWyaujtQo-unsplash.jpg",
    features: ["2 Bedrooms", "Living Room", "Dining Area", "Butler Service"],
    maxGuests: 4,
  },
  {
    id: 4,
    name: "Garden Villa",
    price: 349,
    description:
      "Private villa surrounded by lush gardens, featuring an outdoor seating area and modern amenities.",
    image: "assets/imgs/runnyrem-LfqmND-hym8-unsplash.jpg",
    features: ["Garden Access", "Queen Bed", "Patio", "WiFi"],
    maxGuests: 3,
  },
  {
    id: 5,
    name: "Modern Twin Room",
    price: 249,
    description:
      "Contemporary room with twin beds, ideal for friends or business travelers seeking comfort.",
    image: "assets/imgs/sara-dubler-Koei_7yYtIo-unsplash.jpg",
    features: ["Twin Beds", "Work Desk", "Coffee Maker", "WiFi"],
    maxGuests: 2,
  },
  {
    id: 6,
    name: "Family Penthouse",
    price: 599,
    description:
      "Spacious penthouse perfect for families, with multiple bedrooms and a fully equipped kitchenette.",
    image: "assets/imgs/sidath-vimukthi-60S1280_2i8-unsplash.jpg",
    features: ["3 Bedrooms", "Kitchenette", "Living Area", "Terrace"],
    maxGuests: 6,
  },
];

// Global Variables
let selectedRoom = null;
let reservations = [];

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener("DOMContentLoaded", () => {
  // Load reservations from localStorage
  loadReservations();

  // Display rooms
  displayFeaturedRooms();
  displayAllRooms();
  displayReservations();

  // Set minimum dates for date inputs
  setMinDates();

  // Event Listeners
  setupEventListeners();

  // Smooth scroll for navigation
  setupSmoothScroll();

  // Initialize scroll animations
  initScrollAnimations();
});

// ==========================================
// DISPLAY FUNCTIONS
// ==========================================

// Display Featured Rooms (first 3)
function displayFeaturedRooms() {
  const featuredContainer = document.getElementById("featuredRooms");
  const featured = roomsData.slice(0, 3);

  featuredContainer.innerHTML = featured
    .map((room) => createRoomCard(room))
    .join("");
}

// Display All Rooms
function displayAllRooms() {
  const roomsContainer = document.getElementById("allRooms");
  roomsContainer.innerHTML = roomsData
    .map((room) => createRoomCard(room))
    .join("");
}

// Create Room Card HTML
function createRoomCard(room) {
  return `
        <div class="room-card" onclick="openBookingModal(${room.id})">
            <img src="${room.image}" alt="${room.name}" class="room-image">
            <div class="room-card-content">
                <h3>${room.name}</h3>
                <p class="room-price">$${room.price} <span>/ night</span></p>
                <p class="room-description">${room.description}</p>
                <div class="room-features">
                    ${room.features
                      .map(
                        (feature) =>
                          `<span class="feature-tag">${feature}</span>`
                      )
                      .join("")}
                </div>
                <button class="btn btn-primary">Book Now</button>
            </div>
        </div>
    `;
}

// Display Reservations
function displayReservations() {
  const reservationsContainer = document.getElementById("reservationsList");

  if (reservations.length === 0) {
    reservationsContainer.innerHTML = `
            <div class="empty-reservations">
                <p>📅 You have no reservations yet</p>
                <p>Start exploring our rooms and make your first booking!</p>
            </div>
        `;
    return;
  }

  reservationsContainer.innerHTML = reservations
    .map(
      (reservation, index) => `
        <div class="reservation-card">
            <div class="reservation-info">
                <h3>${reservation.roomName}</h3>
                <p><strong>Guest:</strong> ${reservation.guestName}</p>
                <p><strong>Check-in:</strong> ${formatDate(
                  reservation.checkIn
                )}</p>
                <p><strong>Check-out:</strong> ${formatDate(
                  reservation.checkOut
                )}</p>
                <p><strong>Guests:</strong> ${reservation.guests}</p>
                <p><strong>Total:</strong> $${reservation.totalPrice}</p>
                <span class="reservation-status status-${reservation.status.toLowerCase()}">
                    ${reservation.status}
                </span>
            </div>
            <div class="reservation-actions">
                <button class="btn-cancel" onclick="cancelReservation(${index})">Cancel Reservation</button>
            </div>
        </div>
    `
    )
    .join("");
}

// ==========================================
// BOOKING MODAL
// ==========================================

// Open Booking Modal
function openBookingModal(roomId) {
  selectedRoom = roomsData.find((room) => room.id === roomId);
  if (!selectedRoom) return;

  const modal = document.getElementById("bookingModal");
  const roomInfo = document.getElementById("selectedRoomInfo");

  roomInfo.innerHTML = `
        <h3>${selectedRoom.name}</h3>
        <p>$${selectedRoom.price} per night</p>
    `;

  // Pre-fill dates if available from availability form
  const checkIn = document.getElementById("checkIn").value;
  const checkOut = document.getElementById("checkOut").value;
  const guests = document.getElementById("guests").value;

  if (checkIn) document.getElementById("bookingCheckIn").value = checkIn;
  if (checkOut) document.getElementById("bookingCheckOut").value = checkOut;
  if (guests) document.getElementById("bookingGuests").value = guests;

  modal.classList.add("active");
}

// Close Booking Modal
function closeBookingModal() {
  const modal = document.getElementById("bookingModal");
  modal.classList.remove("active");
  document.getElementById("bookingForm").reset();
  selectedRoom = null;
}

// ==========================================
// FORM SUBMISSIONS
// ==========================================

// Setup Event Listeners
function setupEventListeners() {
  // Availability Form
  document
    .getElementById("availabilityForm")
    .addEventListener("submit", handleAvailabilityCheck);

  // Booking Form
  document
    .getElementById("bookingForm")
    .addEventListener("submit", handleBookingSubmit);

  // Contact Form
  document
    .getElementById("contactForm")
    .addEventListener("submit", handleContactSubmit);

  // Modal Close Buttons
  document
    .querySelector(".close-modal")
    .addEventListener("click", closeBookingModal);
  document
    .getElementById("cancelBooking")
    .addEventListener("click", closeBookingModal);

  // Success Message Close
  document
    .querySelector(".close-success")
    .addEventListener("click", closeSuccessMessage);

  // Close modals on outside click
  window.addEventListener("click", (e) => {
    const modal = document.getElementById("bookingModal");
    const successMsg = document.getElementById("successMessage");

    if (e.target === modal) closeBookingModal();
    if (e.target === successMsg) closeSuccessMessage();
  });

  // Mobile Menu Toggle
  const mobileToggle = document.querySelector(".mobile-menu-toggle");
  const navLinks = document.querySelector(".nav-links");

  mobileToggle.addEventListener("click", () => {
    navLinks.classList.toggle("active");
  });

  // Close mobile menu on link click
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.classList.remove("active");
    });
  });
}

// Handle Availability Check
function handleAvailabilityCheck(e) {
  e.preventDefault();

  const checkIn = document.getElementById("checkIn").value;
  const checkOut = document.getElementById("checkOut").value;
  const guests = document.getElementById("guests").value;

  // Validate dates
  if (!validateDates(checkIn, checkOut)) {
    alert("❌ Check-out date must be after check-in date!");
    return;
  }

  // Scroll to rooms section
  document.getElementById("rooms").scrollIntoView({ behavior: "smooth" });

  // Show success message
  showSuccessMessage(
    `✓ Great! We have rooms available for ${guests} guest(s) from ${formatDate(
      checkIn
    )} to ${formatDate(checkOut)}.`
  );
}

// Handle Booking Submission
function handleBookingSubmit(e) {
  e.preventDefault();

  if (!selectedRoom) return;

  // Get form data
  const formData = {
    roomId: selectedRoom.id,
    roomName: selectedRoom.name,
    roomPrice: selectedRoom.price,
    guestName: document.getElementById("bookingName").value,
    email: document.getElementById("bookingEmail").value,
    phone: document.getElementById("bookingPhone").value,
    checkIn: document.getElementById("bookingCheckIn").value,
    checkOut: document.getElementById("bookingCheckOut").value,
    guests: document.getElementById("bookingGuests").value,
    specialRequests: document.getElementById("specialRequests").value,
    status: Math.random() > 0.5 ? "Confirmed" : "Pending", // Random status for demo
    bookingDate: new Date().toISOString(),
  };

  // Validate form
  if (!validateBookingForm(formData)) return;

  // Calculate total price
  const nights = calculateNights(formData.checkIn, formData.checkOut);
  formData.totalPrice = nights * selectedRoom.price;
  formData.nights = nights;
  // Generate a confirmation code
  formData.confirmationCode = generateConfirmationCode();

  // Add to reservations
  reservations.push(formData);
  saveReservations();

  // Close modal
  closeBookingModal();

  // Show detailed booking confirmation
  showBookingConfirmation(formData);

  // Update reservations display
  displayReservations();

  // Scroll to reservations
  setTimeout(() => {
    document
      .getElementById("reservations")
      .scrollIntoView({ behavior: "smooth" });
  }, 2000);
}

// Handle Contact Form
function handleContactSubmit(e) {
  e.preventDefault();

  const name = document.getElementById("contactName").value;
  const email = document.getElementById("contactEmail").value;

  // Show success message
  showSuccessMessage(
    `✓ Thank you, ${name}! Your message has been sent. We'll respond to ${email} shortly.`
  );

  // Reset form
  document.getElementById("contactForm").reset();
}

// ==========================================
// VALIDATION FUNCTIONS
// ==========================================

// Validate Dates
function validateDates(checkIn, checkOut) {
  const checkInDate = new Date(checkIn);
  const checkOutDate = new Date(checkOut);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (checkInDate < today) {
    alert("❌ Check-in date cannot be in the past!");
    return false;
  }

  if (checkOutDate <= checkInDate) {
    alert("❌ Check-out date must be after check-in date!");
    return false;
  }

  return true;
}

// Validate Booking Form
function validateBookingForm(data) {
  // Check email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(data.email)) {
    alert("❌ Please enter a valid email address!");
    return false;
  }

  // Check phone format (basic)
  const phoneRegex = /^[\d\s\-\+\(\)]+$/;
  if (!phoneRegex.test(data.phone) || data.phone.length < 10) {
    alert("❌ Please enter a valid phone number!");
    return false;
  }

  // Validate dates
  if (!validateDates(data.checkIn, data.checkOut)) {
    return false;
  }

  // Check guest capacity
  if (parseInt(data.guests) > selectedRoom.maxGuests) {
    alert(
      `❌ This room can accommodate maximum ${selectedRoom.maxGuests} guests!`
    );
    return false;
  }

  return true;
}

// ==========================================
// RESERVATION MANAGEMENT
// ==========================================

// Cancel Reservation
function cancelReservation(index) {
  if (confirm("Are you sure you want to cancel this reservation?")) {
    const cancelled = reservations[index];
    reservations.splice(index, 1);
    saveReservations();
    displayReservations();
    showSuccessMessage(
      `Reservation for ${cancelled.roomName} has been cancelled successfully.`
    );
  }
}

// Save Reservations to localStorage
function saveReservations() {
  localStorage.setItem("hotelReservations", JSON.stringify(reservations));
}

// Load Reservations from localStorage
function loadReservations() {
  const stored = localStorage.getItem("hotelReservations");
  if (stored) {
    reservations = JSON.parse(stored);
  }
}

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

// Calculate Number of Nights
function calculateNights(checkIn, checkOut) {
  const checkInDate = new Date(checkIn);
  const checkOutDate = new Date(checkOut);
  const diffTime = Math.abs(checkOutDate - checkInDate);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

// Format Date for Display
function formatDate(dateString) {
  const date = new Date(dateString);
  const options = { year: "numeric", month: "long", day: "numeric" };
  return date.toLocaleDateString("en-US", options);
}

// Set Minimum Dates for Date Inputs
function setMinDates() {
  const today = new Date().toISOString().split("T")[0];

  // Availability form dates
  document.getElementById("checkIn").min = today;
  document.getElementById("checkOut").min = today;

  // Booking form dates
  document.getElementById("bookingCheckIn").min = today;
  document.getElementById("bookingCheckOut").min = today;

  // Update checkout min date when checkin changes
  document.getElementById("checkIn").addEventListener("change", function () {
    document.getElementById("checkOut").min = this.value;
  });

  document
    .getElementById("bookingCheckIn")
    .addEventListener("change", function () {
      document.getElementById("bookingCheckOut").min = this.value;
    });
}

// Generate a short confirmation code
function generateConfirmationCode() {
  const part = Math.random().toString(36).substring(2, 7).toUpperCase();
  const stamp = Date.now().toString(36).toUpperCase().slice(-5);
  return `GLX-${stamp}-${part}`;
}

// Show Success Message
function showSuccessMessage(message) {
  const successMsg = document.getElementById("successMessage");
  const successText = document.getElementById("successText");
  const actions = document.getElementById("successActions");
  if (actions) actions.style.display = "none";

  successText.textContent = message;
  successMsg.classList.add("active");

  // Auto close after 5 seconds
  setTimeout(() => {
    closeSuccessMessage();
  }, 5000);
}

// Close Success Message
function closeSuccessMessage() {
  const successMsg = document.getElementById("successMessage");
  successMsg.classList.remove("active");
}

// Show detailed booking confirmation with actions
function showBookingConfirmation(data) {
  const successMsg = document.getElementById("successMessage");
  const successText = document.getElementById("successText");
  const actions = document.getElementById("successActions");
  const viewBtn = document.getElementById("viewReservationsBtn");
  const closeBtn = document.getElementById("closeSuccessBtn");

  // Build confirmation summary
  const html = `
    <strong>🎉 Booking Confirmed!</strong><br/>
    <span>Confirmation Code: <strong>${
      data.confirmationCode
    }</strong></span><br/>
    <span>Room: <strong>${data.roomName}</strong></span><br/>
    <span>Guest: <strong>${data.guestName}</strong></span><br/>
    <span>Dates: <strong>${formatDate(
      data.checkIn
    )}</strong> — <strong>${formatDate(data.checkOut)}</strong> (${
    data.nights
  } night${data.nights > 1 ? "s" : ""})</span><br/>
    <span>Guests: <strong>${data.guests}</strong></span><br/>
    <span>Total: <strong>$${data.totalPrice}</strong></span><br/>
    <span>Status: <strong>${data.status}</strong></span>
  `;

  // Show content and actions
  successText.innerHTML = html;
  if (actions) actions.style.display = "grid";
  successMsg.classList.add("active");

  // Wire buttons
  if (closeBtn) {
    closeBtn.onclick = () => closeSuccessMessage();
  }
  if (viewBtn) {
    viewBtn.onclick = () => {
      closeSuccessMessage();
      document
        .getElementById("reservations")
        .scrollIntoView({ behavior: "smooth" });
    };
  }
}

// ==========================================
// SMOOTH SCROLL & NAVIGATION
// ==========================================

function setupSmoothScroll() {
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      // Remove active class from all links
      navLinks.forEach((l) => l.classList.remove("active"));

      // Add active class to clicked link
      this.classList.add("active");

      // Get target section
      const targetId = this.getAttribute("href");
      const targetSection = document.querySelector(targetId);

      if (targetSection) {
        const offsetTop = targetSection.offsetTop - 70; // Account for fixed navbar
        window.scrollTo({
          top: offsetTop,
          behavior: "smooth",
        });
      }
    });
  });

  // Update active nav link on scroll
  window.addEventListener("scroll", () => {
    let current = "";
    const sections = document.querySelectorAll("section");

    sections.forEach((section) => {
      const sectionTop = section.offsetTop - 100;
      const sectionHeight = section.clientHeight;

      if (
        window.pageYOffset >= sectionTop &&
        window.pageYOffset < sectionTop + sectionHeight
      ) {
        current = section.getAttribute("id");
      }
    });

    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href") === `#${current}`) {
        link.classList.add("active");
      }
    });
  });
}

// ==========================================
// SCROLL ANIMATIONS
// ==========================================

function initScrollAnimations() {
  // Add scroll-animate class to all animated elements
  const animatedElements = document.querySelectorAll(
    ".room-card, .amenity-card, .section-header, .availability-form, .reservation-card, .contact-info, .contact-form-container, .info-item"
  );

  animatedElements.forEach((el) => {
    el.classList.add("scroll-animate");
  });

  // Create Intersection Observer
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("animate-in");
      }
    });
  }, observerOptions);

  // Observe all animated elements
  animatedElements.forEach((el) => {
    observer.observe(el);
  });
}

// ==========================================
// GLOBAL FUNCTIONS (called from HTML onclick)
// ==========================================

// Make functions globally accessible
window.openBookingModal = openBookingModal;
window.closeBookingModal = closeBookingModal;
window.cancelReservation = cancelReservation;
