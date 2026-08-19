# Nepal Premium Store Invoice System

PHP + MySQL invoice system with Google Sheets sync.

## Important
This application is **not compatible with GitHub Pages** because it requires PHP and MySQL. Use GitHub as the source repository and deploy the PHP application to a PHP/MySQL hosting server.

### Features
- Customer/invoice dashboard
- Google Sheets -> MySQL sync
- Automatic invoice number generation
- Invoice number push-back to Google Sheets
- A4 portrait invoice printing/PDF
- Colorful invoice layout

## Google Sheets
Tab name: `Invoices`

Headers:
`Invoice No | Customer Name | Contact | User ID | Password | Profile | PIN | Item | Plan | Duration | Price | Issue Date | Expiry Date | Status`

The Apps Script in `google-apps-script/Code.gs` is deployed as a Web App. Put the `/exec` URL in the PHP configuration on your hosting server.

## Deployment
1. Create a MySQL database.
2. Import `database/database.sql`.
3. Configure database credentials in your server-side configuration.
4. Set `GOOGLE_SHEET_WEBAPP_URL` to your Apps Script `/exec` URL.
5. Upload the PHP project to your PHP hosting.
6. Start using the public HTTPS URL.

## Security
Do not commit database passwords, private API credentials, or customer data to GitHub. The Google Sheet contains account credentials, so keep the spreadsheet private and restrict access to the Apps Script deployment appropriately.

## Local XAMPP
Copy the folder to `C:/xampp/htdocs/nepal-premium-store`, start Apache/MySQL, import the database if needed, then open `http://localhost/nepal-premium-store/`.
