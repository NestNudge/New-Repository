// ===============================
// WAIT FOR GOOGLE TO LOAD
// ===============================
function waitForGoogle() {
  if (typeof google !== "undefined" && google.maps && google.maps.places) {
    initAutocomplete();
  } else {
    setTimeout(waitForGoogle, 500);
  }
}

// ===============================
// GOOGLE AUTOCOMPLETE
// ===============================
function initAutocomplete() {
  try {
    const input = document.getElementById("address");

    if (!input) {
      console.warn("Address input not found");
      return;
    }

    const autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener("place_changed", () => {
      const place = autocomplete.getPlace();

      if (place && place.formatted_address) {
        input.value = place.formatted_address;
        console.log("✅ Address selected:", place.formatted_address);
      }
    });

    console.log("✅ Autocomplete initialized");

  } catch (err) {
    console.error("Autocomplete error:", err);
  }
}

// ===============================
// FORM SUBMISSION HANDLER
// ===============================
function initFormHandler() {
  const form = document.getElementById("leadForm");

  if (!form) {
    console.error("❌ Form not found");
    return;
  }

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    console.log("🚀 Form submit triggered");

    const data = {
      address: document.getElementById("address")?.value,
      projectType: document.getElementById("projectType")?.value,
      name: document.getElementById("name")?.value,
      email: document.getElementById("email")?.value,
      phone: document.getElementById("phone")?.value
    };

    console.log("📦 Sending data:", data);

    // Basic validation
    if (!data.address || !data.projectType || !data.name || !data.email || !data.phone) {
      alert("Please fill out all fields");
      return;
    }

    try {
      const response = await fetch("https://lead-marketplace.onrender.com/submit-lead", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      console.log("✅ Server response:", result);

      if (response.ok) {
        alert("🎉 You're matched! Pros will contact you.");
        form.reset();
      } else {
        alert("❌ Submission failed");
      }

    } catch (error) {
      console.error("❌ Network error:", error);
      alert("Network error — check connection");
    }
  });
}

// ===============================
// INIT EVERYTHING
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ JS Loaded");

  waitForGoogle();      // 🔥 FIXES AUTOCOMPLETE TIMING
  initFormHandler();    // 🔥 ENSURES FORM WORKS
});
