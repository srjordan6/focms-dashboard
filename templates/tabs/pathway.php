<?php
/**
 * Tab: Pathway (Phase 3.6 placeholder)
 *
 * Will display admissions probability model across 4 pathways:
 *  1. Service Academy (USAFA, USMA, USNA, USCGA, USMMA)
 *  2. ROTC scholarship at top university
 *  3. Academic merit scholarship (Robertson, Morehead, Stamps, Vanderbilt Trustee, etc.)
 *  4. NCAA D1 athletic scholarship
 *
 * Requires upstream work:
 *  - Admissions probability model (weights for GPA, test scores, ECs, leadership, etc.)
 *  - Per-pathway scoring rubric
 *  - Historical baselines for comparison
 *  - Live College Scorecard data integration
 */

if (!defined('ABSPATH')) { exit; }
?>

<div class="focms-banner focms-banner--phase">
    <strong>Phase 3.6 placeholder.</strong>
    The Pathway tab requires an admissions probability model
    not yet built. Significant strategic work upstream.
</div>

<section class="focms-section">
    <h2 class="focms-section-title">Coming in Phase 3.6</h2>
    <p>Four-pathway probability dashboard:</p>
    <ul class="focms-feature-preview-light">
        <li><strong>Service Academy</strong> - USAFA, USMA, USNA, USCGA, USMMA</li>
        <li><strong>ROTC scholarship</strong> at top universities</li>
        <li><strong>Academic merit scholarship</strong> (Robertson, Morehead, Stamps, Vanderbilt Trustee)</li>
        <li><strong>NCAA D1 athletic scholarship</strong></li>
    </ul>
    <p class="focms-muted">
        Each pathway will show probability trend, top-3 strengthening levers, and gap analysis vs. average admitted student.
    </p>
</section>
