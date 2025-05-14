

# **Online Art Auction Platform**

This is a **Full-stack PHP-based Online Art Auction Platform** where users can:

* Browse and bid on artwork.
* Upload their own artwork and manage it.
* Admins (Super Admins) can manage users, artworks, auctions, and transactions.

### **Features**

* **User Management**:

  * User registration and login.
  * Add/remove funds to user accounts.
  * User dashboard to manage their own artwork.

* **Auction Management**:

  * Create and manage live auctions.
  * Place bids on live auctions.
  * Buy artworks directly via the "Buy Now" option.
  * Auctions end automatically when the time is up, and if no bids are placed, the artwork returns to the user’s dashboard for re-auction.

* **Admin (Super Admin) Features**:

  * **Full Control** over users, artworks, auctions, and transactions.
  * Ability to add/remove funds from user accounts.
  * View and manage all transactions.
  * Start, reset, and end auctions.
  * Fake transactions and bids for testing.

### **Tech Stack**

* **Backend**: PHP, MySQL (PDO for database connection).
* **Frontend**: HTML, CSS, JavaScript (Basic UI for interaction).
* **Database**: MySQL with PHPMyAdmin for managing the database schema and data.
* **Server**: XAMPP (for local development environment).

---

## **Installation Guide**

### **Step 1: Download & Setup**

1. **Clone or Download the Repository**:

   ```bash
   git clone https://github.com/mitul37/Simple-Auction-Market-with-PHP-and-HTML-CSS-Java.git
   ```

2. **Set up your Local Development Environment**:

   * **XAMPP**: Install [XAMPP](https://www.apachefriends.org/index.html) to get Apache, MySQL, and PHP running locally.
   * Extract the downloaded project into the **htdocs** folder of your XAMPP directory.
   * Start Apache and MySQL from the XAMPP Control Panel.

3. **Database Setup**:

   * Access **phpMyAdmin** through `http://localhost/phpmyadmin/` (after starting MySQL in XAMPP).
   * Create a new database named `art_auction`.
   * Import the **SQL schema** into phpMyAdmin or create the necessary tables manually (tables like `User`, `Artwork`, `Auction`, `Transaction`, etc.).
   * Update your **database connection settings** in `config.php` to match your local MySQL credentials (typically `root` as the username and no password).

### **Step 2: File Permissions** (If Needed)

1. Set correct file permissions for the `uploads/` directory (where images are stored) to ensure files can be uploaded:

   ```bash
   sudo chmod -R 777 uploads
   ```

### **Step 3: Run the Project**

* Open your browser and go to `http://localhost/art_auction/`.
* You should see the **homepage** of the platform with the **auction market**, where you can explore the artworks.

---

## **Usage**

### **For Users**:

* **Register an Account**: Sign up to become a user and explore available artworks.
* **Place Bids**: Browse live auctions and place bids on artworks you’re interested in.
* **Upload Artwork**: If you're an artist, you can upload your own artwork to be auctioned.
  
---

## **Database Structure**

The project uses the following **tables** in MySQL:

* **User**: Stores information about registered users.
* **Artwork**: Stores information about each artwork, including title, description, image source, and auction status.
* **Auction**: Stores auction details such as start price, reserve price, and auction status.
* **Bid**: Stores bid information for each auction.
* **Transaction**: Logs all transactions (bids, purchases, etc.) within the platform.
* **Admin**: Stores admin user credentials for login.
* **AdminLog**: Logs actions taken by admins and super admins.

### **Relationships**:

* `User` to `Artwork`: One-to-many (a user can have many artworks).
* `Artwork` to `Auction`: One-to-one (an artwork can have one auction).
* `Auction` to `Bid`: One-to-many (an auction can have many bids).

---

## **API Endpoints** (If Applicable)

* **POST** `/place_bid.php`: To place a bid on an auction.
* **POST** `/buy_now.php`: To immediately buy an artwork in the auction.
* **POST** `/upload_artwork.php`: For uploading new artwork to the platform.

---

## **Future Enhancements**

* **Add Payment Gateway Integration**: Enable real-time payment processing for bids and artwork purchases (e.g., Stripe, PayPal).
* **User Rating System**: Allow users to rate artworks and artists.
* **Auction Countdown Timer**: Implement a real-time countdown for auctions on the frontend.
* **Admin Logs**: More detailed logs for Super Admin activities for auditing purposes.

---

## **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

### **Contact**

For any issues, questions, or feedback, feel free to reach out to me at **\[mitul.joarder@g.bracu.ac.bd]**.
