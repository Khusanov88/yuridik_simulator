// ==================== SERVER BILAN ALOQA (UMUMIY) ====================
// So'rovlar CSRF token bilan yuboriladi; server rad etsa, sahifa ko'rsata oladigan javob qaytariladi.
async function apiPost(url, payload) {
    const res = await fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
    });

    if (res.status === 429) {
        return failedResponse("Juda ko'p so'rov yuborildi. Bir daqiqadan so'ng qayta urinib ko'ring.");
    }
    if (res.status === 419) {
        return failedResponse("Sahifa eskirdi. Iltimos, sahifani yangilang.");
    }

    return res.json();
}

function failedResponse(message) {
    return { ok: false, answer: message, error: message, result: message, hint: message, verdict: message };
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.innerText = String(text ?? "");
    return div.innerHTML;
}
