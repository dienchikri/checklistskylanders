


export function generateId(game, section, name) {
    return `checklist-${game}-${section}-${name}`.replace(/\s+/g, "_");
}

export async function saveChecklistState(id, checked, game, section, name) {
    if (window.userChecklist) window.userChecklist[id] = !!checked;

    try {
        const res = await fetch("../api/save_checklist.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                game,
                category: section,
                character: name,
                have: checked ? 1 : 0
            })
        });
        const j = await res.json();
        if (!res.ok) console.warn("save_checklist.php returned non-ok", j);
        return j;
    } catch (err) {
        console.error("Error saving checklist state:", err);
        return { error: err.message };
    }
}

export function loadChecklistState(id) {
    return !!(window.userChecklist && window.userChecklist[id]);
}

export async function migrateLocalStorageToServer() {
    if (!window.localStorage) return;
    const keys = Object.keys(localStorage).filter(k => k.startsWith("checklist-"));
    if (keys.length === 0) return { migrated: 0 };

    const payload = [];
    for (const key of keys) {
        const val = localStorage.getItem(key);
        const checked = val === "1" || val === "true";
        const parts = key.split("-").slice(1);
        const game = parts[0] || "";
        const section = parts[1] || "";
        const name = parts.slice(2).join("-") || "";

        payload.push({ id: key, game, category: section, character: name, have: checked ? 1 : 0 });
    }

    let migrated = 0;
    for (const item of payload) {
        try {
            const res = await fetch("../api/save_checklist.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    game: item.game,
                    category: item.category,
                    character: item.character,
                    have: item.have
                })
            });
            if (res.ok) {
                localStorage.removeItem(item.id);
                migrated++;
            } else {
                console.warn("Migration save failed for", item.id, await res.text());
            }
        } catch (err) {
            console.error("Migration network error for", item.id, err);
        }
    }
    return { migrated };
}
