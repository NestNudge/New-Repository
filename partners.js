const API_BASE = "https://lead-marketplace.onrender.com";

// ===============================
// LOAD LEADS (LOCKED VERSION)
// ===============================
async function loadLeads() {
  const container = document.getElementById("leadsContainer");
  container.innerHTML = "<p>Loading leads...</p>";

  try {
    const res = await fetch(`${API_BASE}/partners`);
    const leads = await res.json();

    container.innerHTML = "";

    if (!leads.length) {
      container.innerHTML = "<p>No leads yet</p>";
      return;
    }

    leads.forEach(lead => {
      const div = document.createElement("div");
      div.className = "lead-card";

      // 🔒 MASK LOCATION (city only)
      const shortAddress = lead.address?.split(",").slice(1, 3).join(",") || "Unknown";

      div.innerHTML = `
        <h3>${lead.projectType.toUpperCase()} Lead</h3>
        <p><strong>Location:</strong> ${shortAddress}</p>

        <p><strong>Name:</strong> 🔒 Locked</p>
        <p><strong>Email:</strong> 🔒 Locked</p>
        <p><strong>Phone:</strong> 🔒 Locked</p>

        <button onclick="unlockLead('${lead.id}')">
          🔓 Unlock Lead – $25
        </button>
      `;

      container.appendChild(div);
    });

  } catch (err) {
    container.innerHTML = "<p>Error loading leads</p>";
  }
}

// ===============================
// UNLOCK LEAD (SIMULATED PAYMENT)
// ===============================
async function unlockLead(leadId) {
  alert("💳 Simulating payment...");

  // 🔥 For now: instantly unlock (later we connect Stripe)

  const res = await fetch(`${API_BASE}/partners`);
  const leads = await res.json();

  const lead = leads.find(l => l.id === leadId);

  if (!lead) {
    alert("Lead not found");
    return;
  }

  alert(`
✅ Lead Unlocked!

Name: ${lead.name}
Email: ${lead.email}
Phone: ${lead.phone}
`);
}

// ===============================
document.addEventListener("DOMContentLoaded", loadLeads);
