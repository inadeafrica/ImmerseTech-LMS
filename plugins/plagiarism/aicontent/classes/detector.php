<?php

namespace plagiarism_aicontent;

defined('MOODLE_INTERNAL') || die();

/**
 * Client for a third-party AI-generated-content detection API.
 *
 * Which provider ImmerseTech settles on isn't decided yet (spec doesn't
 * name one, unlike the similarity checker which explicitly suggests
 * Turnitin/Copyleaks) — this wraps the call behind one method so swapping
 * providers later only touches this class.
 */
class detector {

    protected string $apikey;
    protected string $endpoint;

    public function __construct(string $apikey, string $endpoint) {
        $this->apikey = $apikey;
        $this->endpoint = $endpoint;
    }

    /**
     * Scores a block of submitted text for likely AI-generated content.
     *
     * @param string $text Plain-text extract of the submission (essay,
     *   case analysis, reflection, etc. per spec 5.2 — text-based
     *   submissions only, not calculation sheets/drawings).
     * @return float|null Likelihood in [0.0, 1.0], or null if the
     *   detection call failed (treated as "no score available", not as
     *   a zero/low score).
     */
    public function score(string $text): ?float {
        // TODO: POST $text to the configured provider endpoint with the
        // configured API key via Moodle's \curl class, and map its response
        // to a 0.0-1.0 likelihood. Provider is not chosen yet — see class
        // docblock — so this stays a stub until then.
        throw new \coding_exception('detector::score() is not yet implemented');
    }
}
