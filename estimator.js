// ===============================
// CONFIG
// ===============================
const API_BASE = "https://lead-marketplace.onrender.com";

// ===============================
// GOOGLE AUTOCOMPLETE (SAFE + RELIABLE)
// ===============================
function initAutocomplete() {
  const input = document.getElementById("address");

  if (!input) return;

  const autocomplete = new google.maps.places.Autocomplete(input);

  autocomplete.addListener("place_changed", () => {
    const place = autocomplete.getPlace();
    if (place && place.formatted_address) {
      input.value = place.formatted_address;
    }
  });

  console.log("✅ Autocomplete initialized");
}

// ===============================
// WAIT UNTIL GOOGLE IS READY
// ===============================
function waitForGoogle() {
  if (
    typeof google !== "undefined" &&
    google.maps &&
    google.maps.places &&
    google.maps.places.Autocomplete
  ) {
    initAutocomplete();
  } else {
    setTimeout(waitForGoogle, 300);
  }
}

// ===============================
// FORM SUBMISSION
// ===============================
function initForm() {
  const form = document.getElementById("leadForm");

  if (!form) {
    console.error("❌ Form not found");
    return;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const data = {
      address: document.getElementById("address").value,
      projectType: document.getElementById("projectType").value,
      name: document.getElementById("name").value,
      email: document.getElementById("email").value,
      phone: document.getElementById("phone").value
    };

    try {
      const res = await fetch(`${API_BASE}/submit-lead`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
      });

      if (res.ok) {
        alert("🎉 You're matched!");
        form.reset();
      } else {
        alert("Submission failed");
      }

    } catch (err) {
      alert("Network error");
    }
  });
}

// ===============================
// INIT
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Page loaded");

  waitForGoogle();   // 🔥 KEY FIX
  initForm();
});
