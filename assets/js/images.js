
window.imageFiles = [];

async function loadImageList() {
    try {
        const res = await fetch("../views/get_images.php");
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        window.imageFiles = data;
        console.log(`✅ Loaded ${data.length} images from assets/img`);
    } catch (err) {
        console.error("❌ Failed to load image list:", err);
    }
}

loadImageList();

export function showSkylanderImage(name, row) {
    console.log("🔍 showSkylanderImage triggered for:", name);

    // Remove old preview
    const existingPreview = row.nextElementSibling;
    if (existingPreview && existingPreview.classList.contains("image-preview-row")) {
        existingPreview.remove();
        return;
    }

    const bestMatch = findClosestImage(name);
    const previewRow = document.createElement("tr");
    previewRow.classList.add("image-preview-row");

    const td = document.createElement("td");
    td.colSpan = 2;
    td.style.textAlign = "center";

    if (bestMatch) {
        const img = document.createElement("img");
        img.src = `../assets/img/${bestMatch}`;
        img.alt = name;
        img.classList.add("skylander-preview");
        img.style.maxWidth = "220px";
        img.style.height = "auto";
        img.loading = "lazy";
        td.appendChild(img);
        console.log("Loading image:", img.src);
    } else {
        td.innerHTML = `<em>No image found for "${name}"</em>`;
    }

    previewRow.appendChild(td);
    row.insertAdjacentElement("afterend", previewRow);
}


window.showSkylanderImage = showSkylanderImage;

export function findClosestImage(name) {
    if (!window.imageFiles || imageFiles.length === 0) {
        console.warn("⚠️ No imageFiles loaded or empty");
        return null;
    }

    const normalize = s =>
        s.toLowerCase()
            .replace(/[^a-z0-9]/g, "")
            .replace(/skylander|figure/g, ""); // optional noise words

    const target = normalize(name);

    for (const file of imageFiles) {
        const base = file.replace(/\.(jpg|jpeg|png|webp|gif)$/i, "");
        if (normalize(base) === target) {
            console.log(`🔎 Exact filename match: ${file}`);
            return file;
        }
    }


    const containsCandidates = [];
    for (const file of imageFiles) {
        const base = file.replace(/\.(jpg|jpeg|png|webp|gif)$/i, "");
        const baseNorm = normalize(base);
        if (baseNorm.includes(target) || target.includes(baseNorm)) {
            containsCandidates.push({ file, baseNorm });
        }
    }

    if (containsCandidates.length > 0) {
        let best = null;
        let bestScore = -Infinity;
        for (const c of containsCandidates) {
            const lenDiff = Math.abs(c.baseNorm.length - target.length);

            const score = 0.95 - (lenDiff / Math.max(c.baseNorm.length, target.length)) * 0.25;

            if (c.baseNorm.startsWith(target) || c.baseNorm.endsWith(target)) {

                const boost = 0.02;
                bestScore = bestScore;
                if (score + boost > bestScore) {
                    bestScore = score + boost;
                    best = c.file;
                }
                continue;
            }
            if (score > bestScore) {
                bestScore = score;
                best = c.file;
            }
        }
        console.log(` Contains-based pick: ${best} (score ${bestScore.toFixed(3)})`);
        return best;
    }

    // fallback
    let bestMatch = null;
    let bestScore = 0;
    for (const file of imageFiles) {
        const base = file.replace(/\.(jpg|jpeg|png|webp|gif)$/i, "");
        const baseNorm = normalize(base);
        const sim = similarity(baseNorm, target);
        const lenDiffRatio = Math.abs(baseNorm.length - target.length) / Math.max(baseNorm.length, target.length);

        const score = sim * 0.9 - lenDiffRatio * 0.25;
        if (score > bestScore) {
            bestScore = score;
            bestMatch = file;
        }
    }

    console.log(`🔢 Best fuzzy match for "${name}":`, bestScore.toFixed(3), "→", bestMatch);
    return bestScore > 0.35 ? bestMatch : null;
}

function similarity(a, b) {
    const m = a.length, n = b.length;
    if (m === 0 || n === 0) return 0;
    const dp = Array(m + 1).fill(null).map(() => Array(n + 1).fill(0));
    for (let i = 1; i <= m; i++) {
        for (let j = 1; j <= n; j++) {
            if (a[i - 1] === b[j - 1]) dp[i][j] = dp[i - 1][j - 1] + 1;
            else dp[i][j] = Math.max(dp[i - 1][j], dp[i][j - 1]);
        }
    }
    const lcsLen = dp[m][n];

    const avgLen = (m + n) / 2;
    return lcsLen / avgLen;
}



