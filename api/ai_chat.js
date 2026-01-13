document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("aiForm");
    const input = document.getElementById("aiInput");
    const messages = document.getElementById("aiMessages");

    form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    const sendBtn = form.querySelector("button");

    if (!text || sendBtn.disabled) return; // Prevent double submission

    // Disable button and show loading state
    sendBtn.disabled = true;
    messages.innerHTML += `<div><b>You:</b> ${text}</div>`;
    input.value = "";

    const thinking = document.createElement("div");
    thinking.innerHTML = `<b>ALICE:</b> Thinking...`;
    messages.appendChild(thinking);

    try {
        const response = await fetch("ai_chat.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ message: text })
        });
        const data = await response.json();
        thinking.innerHTML = `<b>ALICE:</b> ${data.reply}`;
    } catch (err) {
        thinking.innerHTML = `<b>ALICE:</b> Connection error.`;
    } finally {
        // Re-enable the button after the API responds
        sendBtn.disabled = false;
    }
})});
