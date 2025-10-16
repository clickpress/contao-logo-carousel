(function () {
    const scrollContainer = document.querySelector(".content-logo-carousel");

    if (!scrollContainer) {
        return false;
    }

    const logoContainer = scrollContainer.querySelector("ul");

    const copy = logoContainer.cloneNode(true);

    copy.setAttribute("aria-hidden", "true");
    scrollContainer.appendChild(copy);
})();


