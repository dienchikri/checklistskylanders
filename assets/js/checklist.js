import {generateId, saveChecklistState, loadChecklistState, migrateLocalStorageToServer} from "./utils.js";
import {showSkylanderImage} from "./images.js";

window.userChecklist = {};

async function init() {
    console.log("Checklist: init");

    try {
        const migrationResult = await migrateLocalStorageToServer();
        if (migrationResult && migrationResult.migrated) {
            console.log("Checklist: migrated localStorage items:", migrationResult.migrated);
        }

        const [checklistRes, userProgressRes] = await Promise.all([
            fetch("../views/data.php"),
            fetch("../api/load_checklist.php"),
        ]);

        if (!checklistRes.ok) throw new Error("Failed to load checklist data.php");
        if (!userProgressRes.ok) throw new Error("Failed to load user progress");

        const checklists = await checklistRes.json();
        const userProgress = await userProgressRes.json();

        window.userChecklist = {};
        userProgress.forEach((item) => {
            const id = generateId(item.game, item.category, item.character);
            window.userChecklist[id] = item.have == 1 || item.have === true;
        });

        renderChecklist(checklists);
    } catch (err) {
        console.error("Checklist initialization error:", err);
        const container = document.getElementById("checklist-container");
        if (container)
            container.innerHTML = `<div class="alert alert-danger">Error loading checklist: ${err.message}</div>`;
    }
}

function renderChecklist(checklists) {
    const container = document.getElementById("checklist-container");
    if (!container) return;

    container.innerHTML = "";

    Object.entries(checklists).forEach(([game, sections]) => {
        const card = document.createElement("div");
        card.className = "card mb-3 shadow-sm game-section";

        const header = document.createElement("div");
        header.className = "game-header";
        header.innerHTML = `<span>${game}</span>`;

        const gameKey = game.toLowerCase();
        if (gameKey.includes("spyro")) header.classList.add("game-spyro");
        else if (gameKey.includes("giant")) header.classList.add("game-giants");
        else if (gameKey.includes("swap")) header.classList.add("game-swap");
        else if (gameKey.includes("trap")) header.classList.add("game-trap");
        else if (gameKey.includes("super")) header.classList.add("game-super");
        else if (gameKey.includes("imagin")) header.classList.add("game-imaginators");

        const body = document.createElement("div");
        body.className = "card-body d-none";

        // Toggle a game's body and update stats
        header.addEventListener("click", () => {
            body.classList.toggle("d-none");
            updateStatsPanel();
        });

        Object.entries(sections).forEach(([sectionName, items]) => {
            const sectionDiv = document.createElement("div");
            sectionDiv.className = "core-section mb-3";

            const sectionHeader = document.createElement("div");
            sectionHeader.className = "section-header fw-bold mb-2";
            sectionHeader.textContent = sectionName;

            const list = document.createElement("ul");
            list.className = "list-unstyled ms-3 skylander-list d-none";


            sectionHeader.addEventListener("click", () => {
                list.classList.toggle("d-none");
                updateStatsPanel();
            });

            items.forEach((char) => {
                const li = document.createElement("li");
                li.className =
                    "skylander-row d-flex align-items-center justify-content-start gap-2";

                const id = generateId(game, sectionName, char.name);
                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.id = id;
                checkbox.checked = loadChecklistState(id);

                checkbox.addEventListener("change", async () => {
                    await saveChecklistState(id, checkbox.checked, game, sectionName, char.name);
                    updateStatsPanel();
                });

                const label = document.createElement("label");
                label.className = "mb-0 flex-grow-1";
                label.textContent = char.name;

                label.addEventListener("click", (e) => {
                    e.preventDefault();
                    showSkylanderImage(char.name, li);
                });

                li.appendChild(checkbox);
                li.appendChild(label);
                list.appendChild(li);
            });

            sectionDiv.appendChild(sectionHeader);
            sectionDiv.appendChild(list);
            body.appendChild(sectionDiv);
        });

        card.appendChild(header);
        card.appendChild(body);
        container.appendChild(card);
    });

    updateStatsPanel();
}


const toggleAllBtn = document.getElementById("toggleAllBtn");
const toggleAllImagesBtn = document.getElementById("toggleAllImagesBtn");
const searchInput = document.getElementById("searchInput");


let expandAllOpen = false;
toggleAllBtn?.addEventListener("click", (e) => {
    expandAllOpen = !expandAllOpen;

    const bodies = document.querySelectorAll(".card-body");
    const lists = document.querySelectorAll(".section-header + ul");

    bodies.forEach((body) => body.classList.toggle("d-none", !expandAllOpen));
    lists.forEach((list) => list.classList.toggle("d-none", !expandAllOpen));

    e.currentTarget.innerHTML = expandAllOpen
        ? '<i class="bi bi-arrows-collapse"></i> Collapse All'
        : '<i class="bi bi-arrows-expand"></i> Expand All';

    updateStatsPanel();
});


let expandImagesOpen = false;
toggleAllImagesBtn?.addEventListener("click", async (e) => {
    expandImagesOpen = !expandImagesOpen;

    document
        .querySelectorAll(".card-body, .section-header + ul")
        .forEach((el) => el.classList.remove("d-none"));

    const allLis = document.querySelectorAll("#checklist-container li");

    if (expandImagesOpen) {
        for (const li of allLis) {
            const label = li.querySelector("label");
            if (!label) continue;
            const next = li.nextElementSibling;
            if (!next || !next.classList.contains("image-preview-row")) {
                const module = await import("./images.js");
                module.showSkylanderImage(label.textContent, li);
            }
        }
    } else {
        document.querySelectorAll(".image-preview-row").forEach((row) => row.remove());
    }

    e.currentTarget.innerHTML = expandImagesOpen
        ? '<i class="bi bi-eye-slash"></i> Hide All Images'
        : '<i class="bi bi-eye"></i> Show All Images';

    updateStatsPanel();
});


searchInput?.addEventListener("input", (e) => {
    const term = e.target.value.trim().toLowerCase();
    const allGames = document.querySelectorAll(".game-section");

    if (term === "") {

        allGames.forEach((game) => {
            const body = game.querySelector(".card-body");
            if (body) body.classList.add("d-none");
            game.classList.remove("d-none");
            game.querySelectorAll(".core-section").forEach((c) => c.classList.remove("d-none"));
            game.querySelectorAll("li").forEach((li) => li.classList.remove("d-none"));
        });
        document.querySelectorAll(".image-preview-row").forEach((r) => r.remove());
        updateStatsPanel();
        return;
    }

    allGames.forEach((game) => {
        const body = game.querySelector(".card-body");
        if (body) body.classList.remove("d-none");

        let gameHasVisible = false;

        game.querySelectorAll(".core-section").forEach((section) => {
            const list = section.querySelector("ul");
            section.classList.remove("d-none");
            list.classList.remove("d-none");

            let sectionHasVisible = false;

            list.querySelectorAll("li").forEach((li) => {
                const label = li.querySelector("label");
                if (!label) return;
                const name = label.textContent.toLowerCase();
                const match = name.includes(term);

                li.classList.toggle("d-none", !match);
                if (match) {
                    sectionHasVisible = true;
                    const next = li.nextElementSibling;
                    if (!next || !next.classList.contains("image-preview-row")) {
                        import("./images.js").then((module) =>
                            module.showSkylanderImage(label.textContent, li)
                        );
                    }
                } else if (li.nextElementSibling?.classList.contains("image-preview-row")) {
                    li.nextElementSibling.classList.add("d-none");
                }
            });

            if (!sectionHasVisible) section.classList.add("d-none");
            gameHasVisible = gameHasVisible || sectionHasVisible;
        });

        game.classList.toggle("d-none", !gameHasVisible);
    });

    updateStatsPanel();
});

function updateStatsPanel() {
    const checkboxes = document.querySelectorAll("#checklist-container input[type='checkbox']");
    const checked = Array.from(checkboxes).filter((cb) => cb.checked).length;
    const total = checkboxes.length;
    const unchecked = total - checked;

    const visibleLis = Array.from(
        document.querySelectorAll("#checklist-container li")
    ).filter((li) => {
        // climb up the DOM to see if any parent (li, ul, div, etc.) is hidden
        let el = li;
        while (el && el.id !== "checklist-container") {
            if (el.classList.contains("d-none")) return false;
            el = el.parentElement;
        }
        return true;
    });

    const visible = visibleLis.length;

    document.getElementById("checkedCount").textContent = checked;
    document.getElementById("uncheckedCount").textContent = unchecked;
    document.getElementById("visibleCount").textContent = visible;
}

document.addEventListener("change", (e) => {
    if (e.target.matches("#checklist-container input[type='checkbox']")) {
        updateStatsPanel();
    }
});


if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}
