from playwright.sync_api import sync_playwright, expect

def run_verification():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # Log in
        page.goto("http://localhost:8000/login.php")
        page.get_by_label("Username").fill("testuser")
        page.get_by_label("Password").fill("password")
        page.get_by_role("button", name="Login").click()
        expect(page).to_have_title("Dashboard - Siva Ganga")

        # Navigate to blog management
        page.get_by_role("link", name="Blog Management").click()
        expect(page).to_have_title("Blog Management - Siva Ganga")

        # Create a new post
        page.get_by_role("button", name="Create New Post").click()
        page.get_by_label("Title").fill("Formatted Blog Post")

        # Wait for the editor to be ready
        page.wait_for_selector("iframe[title='Rich Text Area']")
        page.wait_for_timeout(1000) # 1 second delay

        # Enter formatted content into TinyMCE
        page.evaluate("tinymce.get('content').setContent('This is some <b>bold</b> and <i>italic</i> text.')")

        page.get_by_label("Status").select_option("published")
        page.get_by_role("button", name="Save Post").click()
        page.locator(".modal-footer").get_by_role("button", name="Close").click()
        page.screenshot(path="jules-scratch/verification/debug.png")
        page.wait_for_url("http://localhost:8000/admin_blog.php")

        # Verify the post was created
        expect(page.get_by_text("Formatted Blog Post")).to_be_visible()

        # Go to the public blog page
        page.goto("http://localhost:8000/blog.php")
        expect(page).to_have_title("Blog - Siva Ganga")

        # Verify the new post is listed
        expect(page.get_by_role("heading", name="Formatted Blog Post")).to_be_visible()

        # Click to view the full post
        page.get_by_role("link", name="Read More").click()
        expect(page).to_have_title("Formatted Blog Post - Siva Ganga")

        # Verify the full post content
        expect(page.get_by_role("heading", name="Formatted Blog Post")).to_be_visible()

        # Take a screenshot
        page.screenshot(path="jules-scratch/verification/verification.png")

        browser.close()

if __name__ == "__main__":
    run_verification()