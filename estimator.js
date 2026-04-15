// ===============================
// CONFIG
// ===============================
const API_BASE = "https://lead-marketplace.onrender.com";


// ===============================
// GLOBAL ADDRESS STORAGE
// ===============================
let selectedAddress = "";


// ===============================
// NEW GOOGLE AUTOCOMPLETE (MODERN)
// ===============================
function initAutocomplete() {
  const placeElement = document.querySelector("gmp-place-autocomplete");

  if (!placeElement) {
    console.warn("❌ Autocomplete element not found");
    return;
  }

  // Listen for place selection
  placeElement.addEventListener("gmp-placeselect", (event) => {
    const place = event.detail.place;

    if (place && place.formattedAddress) {
      selectedAddress = place.formattedAddress;
      console.log("✅ Address selected:", selectedAddress);
    }
  });

  console.log("✅ New Autocomplete initialized");
}


// ===============================
// FORM HANDLER
// ===============================
function initForm() {
  const form = document.getElementById("leadForm");

  if (!form) {
    console.error("❌ Form not found");
    return;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    console.log("🚀 Form submit triggered");

    // Fallback: if user typed but didn’t select suggestion
    const placeElement = document.querySelector("gmp-place-autocomplete");
    const fallbackAddress = placeElement?.value || "";

    const data = {
      address: selectedAddress || fallbackAddress,
      projectType: document.getElementById("projectType").value,
      name: document.getElementById("name").value.trim(),
      email: document.getElementById("email").value.trim(),
      phone: document.getElementById("phone").value.trim()
    };

    // 🔒 Basic validation
    if (!data.address || !data.projectType || !data.name || !data.email || !data.phone) {
      alert("Please complete all fields");
      return;
    }

    try {
      const res = await fetch(`${API_BASE}/submit-lead`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
      });

      if (res.ok) {
        alert("🎉 You're matched! Pros will contact you.");
        form.reset();
        selectedAddress = ""; // reset stored address
      } else {
        const errorText = await res.text();
        console.error("❌ Server error:", errorText);
        alert("Submission failed");
      }

    } catch (err) {
      console.error("❌ Network error:", err);
      alert("Network error — check connection");
    }
  });
}


// ===============================
// INIT APP
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Page loaded");

  // Wait a tiny bit for Google script to load
  setTimeout(() => {
    initAutocomplete();
  }, 500);

  initForm();
});

// ===============================
// CHAT ASSISTANT
// ===============================

// Toggle chat
document.getElementById("chat-button").addEventListener("click", () => {
  document.getElementById("chat-box").classList.toggle("hidden");
});

// Handle selection
function selectOption(type) {
  document.getElementById("projectType").value = type;

  const response = document.getElementById("chat-response");

  response.innerHTML = `
    <p>Great choice 👍</p>
    <p>Now enter your address above to get matched instantly.</p>
  `;

  // Scroll to form
  document.getElementById("address").scrollIntoView({ behavior: "smooth" });
}

const popupData = [
  {name:"Michael B.", location:"FL", action:"Signed up as a Partner", icon:"🤝"},
  {name:"Sarah K.", location:"TX", action:"Requested Roof Quote", icon:"🏠"},
  {name:"James T.", location:"CA", action:"Checked Financing Options", icon:"💰"},
  {name:"Emily R.", location:"NY", action:"Viewed Impact Windows", icon:"🪟"},
  {name:"Chris D.", location:"AZ", action:"Joined NestNudge", icon:"✅"},
  {name:"Daniel R.", location:"FL", action:"Compared Roofing Options", icon:"🧱"},
  {name:"Melissa T.", location:"TX", action:"Explored Home Savings", icon:"📊"}
];
