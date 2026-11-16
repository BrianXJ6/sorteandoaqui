<?php

return [

    'agents' => [
        'claude_code' => [
            // Boost picks CLAUDE.md whenever it exists. CLAUDE.md is our own pt_BR
            // document, so the generated guidelines go to AGENTS.md instead.
            'guidelines_path' => 'AGENTS.md',
        ],
    ],

];
