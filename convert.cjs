const fs = require('fs');

function sqlToPhp(filename, outname) {
    const sql = fs.readFileSync(filename, 'utf-8');
    const insertRegex = /INSERT INTO `([^`]+)` \(([^)]+)\) VALUES\s*([\s\S]+?);/g;
    
    let phpCode = `<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Support\\Facades\\DB;\n\nreturn new class extends Migration\n{\n    public function up()\n    {\n`;
    
    let match;
    while ((match = insertRegex.exec(sql)) !== null) {
        const table = match[1];
        const cols = match[2].split(',').map(c => c.trim().replace(/`/g, ''));
        const valuesRaw = match[3];
        
        let tuples = [];
        let currentTuple = [];
        let currentVal = '';
        let inString = false;
        let inTuple = false;
        
        for (let i = 0; i < valuesRaw.length; i++) {
            const char = valuesRaw[i];
            
            if (char === "'" && (i === 0 || valuesRaw[i-1] !== '\\')) {
                if (inTuple) {
                    inString = !inString;
                    currentVal += char;
                }
            } else if (!inString && char === '(') {
                inTuple = true;
                currentTuple = [];
                currentVal = '';
            } else if (!inString && char === ')') {
                inTuple = false;
                if (currentVal.trim() !== '') currentTuple.push(currentVal.trim());
                tuples.push(currentTuple);
                currentVal = '';
            } else if (!inString && char === ',') {
                if (inTuple) {
                    currentTuple.push(currentVal.trim());
                    currentVal = '';
                }
            } else {
                if (inTuple) {
                    currentVal += char;
                }
            }
        }
        
        // Filter out any completely empty tuples or tuples that don't match column count
        tuples = tuples.filter(t => t.length === cols.length);

        let chunks = [];
        for (let i=0; i<tuples.length; i+=100) {
            chunks.push(tuples.slice(i, i+100));
        }

        for (let chunk of chunks) {
            phpCode += `        DB::table('${table}')->insert([\n`;
            for (let t of chunk) {
                phpCode += `            [\n`;
                for (let i = 0; i < cols.length; i++) {
                    let val = t[i];
                    if (val === undefined) val = 'null';
                    if (val === 'NOW()') val = 'now()';
                    else if (val === 'NULL') val = 'null';
                    phpCode += `                '${cols[i]}' => ${val},\n`;
                }
                phpCode += `            ],\n`;
            }
            phpCode += `        ]);\n\n`;
        }
    }
    
    phpCode += `    }\n\n    public function down()\n    {\n        // Hapus data jika migrasi dibatalkan\n`;
    
    let tables = [...sql.matchAll(/INSERT INTO `([^`]+)`/g)].map(m => m[1]);
    let uniqueTables = [...new Set(tables)].reverse();
    for(let table of uniqueTables) {
        phpCode += `        DB::table('${table}')->truncate();\n`;
    }
    
    phpCode += `    }\n};\n`;
    
    fs.writeFileSync(outname, phpCode);
    console.log("Converted " + filename + " to " + outname);
}

sqlToPhp('ahp bobot.sql', 'database/migrations/2026_06_16_000001_insert_ahp_bobot_data.php');
sqlToPhp('kurasi.sql', 'database/migrations/2026_06_16_000002_insert_kurasi_data.php');
