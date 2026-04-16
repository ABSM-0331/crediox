(function () {
    const TOAST_CONTAINER_ID = "gp-toast-container";
    const DISPLAY_TIME = 3200;

    function getContainer() {
        let container = document.getElementById(TOAST_CONTAINER_ID);
        if (!container) {
            container = document.createElement("div");
            container.id = TOAST_CONTAINER_ID;
            container.className = "toast-container";
            document.body.appendChild(container);
        }
        return container;
    }

    function getIcon(type) {
        return type === "error" ? "!" : "✓";
    }

    function getTitle(type) {
        return type === "error" ? "Error" : "Éxito";
    }

    function closeToast(toast) {
        if (!toast || toast.dataset.closing === "true") {
            return;
        }

        toast.dataset.closing = "true";
        toast.style.animation = "toast-out 0.18s ease forwards";
        window.setTimeout(() => {
            toast.remove();
        }, 180);
    }

    function showToast(message, type = "success") {
        if (!message) {
            return null;
        }

        const normalizedType = type === "error" ? "error" : "success";
        const container = getContainer();
        const toast = document.createElement("div");
        toast.className = `toast-notification toast-${normalizedType}`;
        toast.setAttribute("role", "alert");
        toast.setAttribute("aria-live", "assertive");
        toast.innerHTML = `
            <span class="toast-icon" aria-hidden="true">${getIcon(normalizedType)}</span>
            <div class="toast-content">
                <span class="toast-title">${getTitle(normalizedType)}</span>
                <span class="toast-message"></span>
            </div>
            <button type="button" class="toast-close" aria-label="Cerrar">&times;</button>
        `;

        toast.querySelector(".toast-message").textContent = message;
        toast
            .querySelector(".toast-close")
            .addEventListener("click", () => closeToast(toast));
        container.appendChild(toast);

        window.setTimeout(() => closeToast(toast), DISPLAY_TIME);
        return toast;
    }

    function showToastFromDataset() {
        if (!document.body) {
            return;
        }

        const message = document.body.dataset.toastMessage;
        const type = document.body.dataset.toastType || "success";
        if (message) {
            showToast(message, type);
            document.body.dataset.toastMessage = "";
            document.body.dataset.toastType = "";
        }
    }

    window.showToast = showToast;

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", showToastFromDataset);
    } else {
        showToastFromDataset();
    }
})();
