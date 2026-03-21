// ===============================
// CONFIG (YOUR LIVE BACKEND)
// ===============================
const API_BASE = "https://lead-marketplace.onrender.com";


// ===============================
// LOAD LEADS FROM BACKEND
// ===============================
async function loadLeads() {
  const container = document.getElementById("leadsContainer");

  if (!container) {
    console.error("❌ leadsContainer not found");
    return;
  }

  container.innerHTML = "<p>Loading leads...</p>";

  try {
    const response = await fetch(`${API_BASE}/partners`);

    if (!response.ok) {
      throw new Error(`Server error: ${response.status}`);
    }

    const leads = await response.json();

    console.log("✅ Leads loaded:", leads);

    container.innerHTML = "";

    if (!leads || leads.length === 0) {
      container.innerHTML = "<p>No leads available yet.</p>";
      return;
    }

    leads.forEach(lead => {
      const div = document.createElement("div");
      div.className = "lead-card";

      div.innerHTML = `
        <h3>${lead.projectType || "Project"}</h3>
        <p><strong>Address:</strong> ${lead.address || "N/A"}</p>
        <p><strong>Name:</strong> ${lead.name || "N/A"}</p>
        <p><strong>Email:</strong> ${lead.email || "N/A"}</p>
        <p><strong>Phone:</strong> ${lead.phone || "N/A"}</p>

        <input type="number" placeholder="Enter your bid $" id="bid-${lead.id}">
        <button onclick="submitBid('${lead.id}')">Place Bid</button>
      `;

      container.appendChild(div);
    });

  } catch (error) {
    console.error("❌ Error loading leads:", error);
    container.innerHTML = "<p>Failed to load leads. Check console.</p>";
  }
}


// ===============================
// SUBMIT BID
// ===============================
async function submitBid(leadId) {
  const input = document.getElementById(`bid-${leadId}`);

  if (!input) {
    alert("Bid input not found");
    return;
  }

  const amount = input.value;

  if (!amount || amount <= 0) {
    alert("Enter a valid bid amount");
    return;
  }

  const data = {
    lead_id: leadId,
    partner_id: "partner123", // placeholder (we add login later)
    amount: amount
  };

  try {
    const response = await fetch(`${API_BASE}/bids`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    });

    if (response.ok) {
      alert("💰 Bid placed successfully!");
      input.value = "";
    } else {
      const errText = await response.text();
      console.error("❌ Bid error:", errText);
      alert("Bid failed");
    }

  } catch (error) {
    console.error("❌ Network error:", error);
    alert("Network error — try again");
  }
}


// ===============================
// INIT
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ Partner dashboard loaded");
  loadLeads();
});
