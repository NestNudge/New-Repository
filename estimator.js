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
