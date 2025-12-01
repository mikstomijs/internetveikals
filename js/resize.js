function handleResize() {
    const filterPanel = document.querySelector(".container_filter");


    if (window.innerWidth <= 540) {
        filterPanel.style.display = "none";

    } else {
        filterPanel.style.display = "flex";
    }
}

window.addEventListener("resize", handleResize);
window.addEventListener("load", handleResize);