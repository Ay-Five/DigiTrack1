<?php
require_once __DIR__ . '/Database.php';

/**
 * BaseDashboard.php
 *
 * OOP PILLARS: ABSTRACTION + INHERITANCE
 * ----------------------------------------
 * ABSTRACTION:
 *   This abstract class defines the "contract" that every dashboard must
 *   fulfill without dictating HOW each one does it.  The two abstract
 *   methods — handlePost() and loadData() — must be implemented by every
 *   concrete subclass.  Callers only need to know these two methods exist;
 *   the internals stay hidden.
 *
 * INHERITANCE:
 *   AdminDashboard, InstructorDashboard, and StudentDashboard all extend
 *   this class.  They automatically get:
 *     - $this->db       (Database instance)
 *     - $this->userId   (the authenticated user's ID)
 *     - $this->today    (today's date string)
 *     - $this->data[]   (populated after loadData() runs)
 *     - redirect()      (shared helper for POST-redirect-GET)
 *     - notify()        (shared helper to insert a notification row)
 *     - cColors()       (shared course-colour map)
 *     - statusMap()     (shared status CSS/label maps)
 *   ...without rewriting a single line in each subclass.
 */
abstract class BaseDashboard
{
    /**
     * ═══════════════════════════════════════════════════════════════════
     * TESTING MODE — flip this to `true` to temporarily bypass date-sensitive
     * booking rules (TDC Saturday-only, PDC Tue/Thu or Tue/Wed start days,
     * and the "wait until the package's last day" gate before an instructor
     * can log a session). This lets you test the full booking → grading →
     * rating flow immediately without waiting for real calendar days to pass.
     *
     * ⚠️ MUST be set back to `false` before going live — while true, none
     * of the weekday scheduling rules are enforced. ⚠️
     * ═══════════════════════════════════════════════════════════════════
     */
    public const TESTING_MODE = True;

    // ── Shared state (protected = visible to child classes, not outside) ──
    protected Database $db;
    protected int      $userId;
    protected string   $today;
    protected array    $data = [];   // populated by loadData()

    /** Course-type → CSS tag class map shared by all dashboards. */
    protected const COURSE_COLORS = [
        'TDC Face-to-Face'       => 'tag-amber',
        'TDC Online'             => 'tag-blue',
        'PDC Car Manual'         => 'tag-green',
        'PDC Car Automatic'      => 'tag-teal',
        'PDC Motorcycle Manual'  => 'tag-coral',
        'PDC Motorcycle Automatic' => 'tag-purple',
    ];

    /** Appointment status → CSS class */
    protected const STATUS_CSS = [
        'pending'     => 'bkp',
        'approved'    => 'bksu',
        'in_progress' => 'bkb',
        'completed'   => 'bkg',
        'rejected'    => 'bkr',
        'cancelled'   => 'bkd',
    ];

    /** Appointment status → human label */
    protected const STATUS_LABEL = [
        'pending'     => 'Pending',
        'approved'    => 'Approved',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'rejected'    => 'Rejected',
        'cancelled'   => 'Cancelled',
    ];

    // ── Constructor ───────────────────────────────────────────────────────

    public function __construct(Database $db, int $userId)
    {
        $this->db     = $db;
        $this->userId = $userId;
        $this->today  = date('Y-m-d');
    }

    // ── Abstract methods (ABSTRACTION) ────────────────────────────────────

    /**
     * Handle a POST action (approve, delete, send message, etc.).
     * Each subclass implements only the actions relevant to its role.
     */
    abstract public function handlePost(string $action): void;

    /**
     * Run all SELECT queries and store results in $this->data[].
     * Each subclass fetches only what its view needs.
     */
    abstract public function loadData(): void;

    // ── Shared concrete helpers (INHERITANCE) ─────────────────────────────

    /**
     * POST → Redirect → GET pattern.
     * Sets a flash message in the session then redirects.
     */
    protected function redirect(string $url, string $flash = '', bool $isError = false): void
    {
        if ($flash !== '') {
            $_SESSION[$isError ? 'flash_err' : 'flash'] = $flash;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Insert a notification row for any user type.
     * Shared so every subclass sends notifications the same way.
     */
    protected function notify(int $userId, string $userType, string $message, string $type): void
    {
        $msg  = $this->db->escape($message);
        $ut   = $this->db->escape($userType);
        $tp   = $this->db->escape($type);
        $this->db->query(
            "INSERT INTO notifications (user_id,user_type,message,type)
             VALUES ($userId,'$ut','$msg','$tp')"
        );
    }

    /**
     * Resolve active section from the URL query string.
     * Strips any characters that aren't lowercase letters or underscores.
     */
    protected function resolveSection(string $default = 'dashboard'): string
    {
        if (!empty($_GET['section'])) {
            return preg_replace('/[^a-z_]/', '', $_GET['section']);
        }
        return $default;
    }

    /**
     * Build 6-month chart labels (e.g. ["Dec 2024", …, "May 2025"]).
     * Shared across all dashboards that show monthly trend charts.
     */
    protected function buildMonthLabels(): array
    {
        $labels = [];
        for ($m = 5; $m >= 0; $m--) {
            $labels[] = date('M Y', strtotime("-$m months"));
        }
        return $labels;
    }

    /** Render a ★ / ☆ star string for a 1–5 rating. */
    public static function stars(int $n): string
    {
        $s = '';
        for ($i = 1; $i <= 5; $i++) {
            $s .= $i <= $n ? '★' : '☆';
        }
        return $s;
    }

    /** Return the course-colour map (used in dashboard view files). */
    public function getCourseColors(): array
    {
        return self::COURSE_COLORS;
    }

    /** Return the status CSS map. */
    public function getStatusCss(): array
    {
        return self::STATUS_CSS;
    }

    /** Return the status label map. */
    public function getStatusLabels(): array
    {
        return self::STATUS_LABEL;
    }

    /** Return all loaded data (called by dashboard view files). */
    public function getData(): array
    {
        return $this->data;
    }
}
