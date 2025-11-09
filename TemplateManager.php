<?php
require_once 'db.php';

class TemplateManager {
    private $connection;

    public function __construct() {
        $this->connection = db_connect();
    }

    public function __destruct() {
        mysqli_close($this->connection);
    }

    /**
     * Creates a new email template.
     *
     * @param string $name
     * @param string $subject
     * @param string $body
     * @param string $associated_event
     * @return bool
     */
    public function createTemplate($name, $subject, $body, $associated_event) {
        $query = "INSERT INTO email_templates (name, subject, body, associated_event) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $subject, $body, $associated_event);
        return mysqli_stmt_execute($stmt);
    }

    /**
     * Fetches a single template by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getTemplateById($id) {
        $query = "SELECT * FROM email_templates WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    /**
     * Fetches a single active template by its associated event.
     *
     * @param string $event_name
     * @return array|null
     */
    public function getTemplateByEvent($event_name) {
        $query = "SELECT * FROM email_templates WHERE associated_event = ? AND is_active = 1 LIMIT 1";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $event_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    /**
     * Fetches all email templates.
     *
     * @return array
     */
    public function getAllTemplates() {
        $query = "SELECT * FROM email_templates ORDER BY created_at DESC";
        $result = mysqli_query($this->connection, $query);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    /**
     * Updates an existing email template.
     *
     * @param int $id
     * @param string $name
     * @param string $subject
     * @param string $body
     * @param string $associated_event
     * @param int $is_active
     * @return bool
     */
    public function updateTemplate($id, $name, $subject, $body, $associated_event, $is_active) {
        $query = "UPDATE email_templates SET name = ?, subject = ?, body = ?, associated_event = ?, is_active = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ssssii", $name, $subject, $body, $associated_event, $is_active, $id);
        return mysqli_stmt_execute($stmt);
    }

    /**
     * Deletes an email template by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteTemplate($id) {
        $query = "DELETE FROM email_templates WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        return mysqli_stmt_execute($stmt);
    }
}
