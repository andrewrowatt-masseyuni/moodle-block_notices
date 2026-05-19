import { expect, Page, test } from '@playwright/test';

const ADMIN_USER = process.env.MOODLE_ADMIN_USER ?? 'admin';
const ADMIN_PASSWORD = process.env.MOODLE_ADMIN_PASSWORD ?? 'admin';

async function loginAsAdmin(page: Page) {
  await page.goto('/login/index.php');
  await page.locator('#username').fill(ADMIN_USER);
  await page.locator('#password').fill(ADMIN_PASSWORD);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.locator('#loginbtn').click(),
  ]);
}

async function turnEditingModeOn(page: Page) {
  // Moodle 4.x renders the editing-mode toggle as a real <input type="checkbox">
  // (name="setmode") with an associated <label>Edit mode</label>. Clicking the
  // checkbox submits the parent form and reloads the page in edit mode.
  const toggle = page.getByLabel('Edit mode', { exact: true });
  if (await toggle.count()) {
    if (!(await toggle.isChecked())) {
      await Promise.all([
        page.waitForLoadState('networkidle'),
        toggle.check(),
      ]);
    }
    return;
  }
  // Older themes render a "Turn editing on" link/button instead.
  const fallback = page.getByRole('button', { name: /turn editing on/i });
  if (await fallback.count()) {
    await fallback.click();
    await page.waitForLoadState('networkidle');
  }
}

async function addNoticesBlock(page: Page) {
  // The "Add a block" entry sits in the blocks drawer on the site homepage.
  // It opens a modal listing all available blocks; click "Notices".
  await page.getByRole('link', { name: /add a block/i }).first().click();
  await page.getByRole('link', { name: 'Notices', exact: true }).click();
  await page.waitForLoadState('networkidle');
}

async function setQuillContent(page: Page, value: string) {
  await page.locator('.block_notices-quill-editor .ql-editor').waitFor();
  await page.evaluate((val) => {
    const editor = document.querySelector<HTMLElement>('.block_notices-quill-editor .ql-editor');
    const textarea = document.querySelector<HTMLTextAreaElement>('textarea[data-block-notices-quill]');
    if (!editor || !textarea) {
      throw new Error('Notice Quill editor not found');
    }
    editor.innerHTML = `<p>${val}</p>`;
    textarea.value = editor.innerHTML;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
  }, value);
}

test('admin can add a notice', async ({ page }) => {
  // --- Background ---
  // Given I log in as "admin"
  await loginAsAdmin(page);

  // And I am on site homepage
  await page.goto('/my/indexsys.php');

  // And I turn editing mode on
  await turnEditingModeOn(page);

  // When I add the "Notices" block
  await addNoticesBlock(page);

  // Then I should see "There are no notices. Have a great day!"
  await expect(page.getByText('There are no notices. Have a great day!')).toBeVisible();

  // And I should see "Manage notices"
  const manageLink = page.getByRole('button', { name: 'Manage notices' });
  await expect(manageLink).toBeVisible();

  await page.getByRole('button', { name: 'Reset Dashboard for all users' }).click();
  await page.getByRole('button', { name: 'Continue' }).click();

  // --- Scenario ---
  // And I am on site homepage
  await page.goto('/');

  // And I follow "Manage notices"
  await page.getByRole('button', { name: 'Manage notices' }).click();
  await expect(page).toHaveURL(/\/blocks\/notices\/manage\.php/);

  // And I should see "No notices" in each of the three visibility group counts.
  await expect(
    page.locator('.block-notices-group-visibility-preview .block-notices-count'),
  ).toContainText('No notices');
  await expect(
    page.locator('.block-notices-group-visibility-visible .block-notices-count'),
  ).toContainText('No notices');
  await expect(
    page.locator('.block-notices-group-visibility-hidden .block-notices-count'),
  ).toContainText('No notices');

  // And I follow "Add notice"
  await page.getByRole('button', { name: 'Add notice' }).click();

  // And I should see "Add notice" (modal dialog).
  const dialog = page.getByRole('dialog');
  await expect(dialog).toContainText('Add notice');

  // And I set the following fields to these values:
  //   | Staff only | No |  -> ensure unchecked
  const staffonly = dialog.locator('input[name="staffonly"]');
  if (await staffonly.isChecked()) {
    await staffonly.uncheck();
  }
  await dialog.locator('input[name="title"]').fill('Notice1title');
  await dialog.locator('input[name="moreinformationurl"]').fill('http://massey.ac.nz');
  await dialog.locator('input[name="owner"]').fill('notice1owner');
  await dialog.locator('input[name="owneremail"]').fill('Notice1owneremail@noreply.com');
  await dialog.locator('input[name="notes"]').fill('Remove 1 November 2024');

  // And I set the notice Quill editor to "Notice1content"
  await setQuillContent(page, 'Notice1content');

  // And I press "Save"
  await dialog.getByRole('button', { name: 'Save' }).click();
  await expect(dialog).toBeHidden();

  // And I should see "Manage notices"
  await expect(page.getByRole('heading', { name: 'Manage notices' })).toBeVisible();

  // Then I should see "Notice1title"
  await expect(page.getByText('Notice1title')).toBeVisible();
});
