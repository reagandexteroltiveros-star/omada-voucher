const express = require("express");
const axios = require("axios");

const app = express();
app.use(express.json());

const BOT_TOKEN = process.env.BOT_TOKEN;
const CHAT_ID = process.env.CHAT_ID;

app.post("/voucher", async (req, res) => {
  try {
    const { username, ip, mac } = req.body;

    const time = new Date().toISOString();

    const message = `
📶 WiFi Voucher Connected

👤 User: ${username || "Unknown"}
🌐 IP: ${ip || "Unknown"}
🖥 MAC: ${mac || "Unknown"}
⏰ Time: ${time}
`;

    await axios.post(
      `https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`,
      {
        chat_id: CHAT_ID,
        text: message,
      }
    );

    res.status(200).send("Alert sent to Telegram");
  } catch (error) {
    console.error(error.message);
    res.status(500).send("Error sending alert");
  }
});

app.listen(3000, () => {
  console.log("Webhook server running on port 3000");
});
