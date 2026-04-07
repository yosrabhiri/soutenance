from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from bs4 import BeautifulSoup
import mysql.connector
import time
import re
import unicodedata

# -------------------------------
# 1. Connexion à la base MySQL
# -------------------------------
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="stage5"
)
cursor = db.cursor()

# Table societes (déjà créée)
cursor.execute("""
CREATE TABLE IF NOT EXISTS societes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255),
    adresse TEXT,
    secteur_activite VARCHAR(255),
    telephone VARCHAR(100),
    email VARCHAR(255),
    site_web VARCHAR(255),
    lien_linkedin VARCHAR(255)
)
""")

# -------------------------------
# 2. Fonction utilitaire
# -------------------------------
def clean_key(text):
    """Normaliser les clés (enlever accents, espaces, tirets spéciaux)"""
    text = ''.join(c for c in unicodedata.normalize('NFD', text)
                   if unicodedata.category(c) != 'Mn')
    text = text.lower()
    text = re.sub(r"[\s–—‐-]+", "-", text)  # tous les tirets → "-"
    return text

# -------------------------------
# 3. Configurer Selenium
# -------------------------------
options = Options()
options.headless = False  # True = mode sans interface
driver = webdriver.Chrome(options=options)

driver.get("https://www.tunisieindustrie.nat.tn/fr/dbs.asp")
time.sleep(2)

# -------------------------------
# 4. Sélectionner le secteur
# -------------------------------
select = Select(driver.find_element(By.NAME, "secteur"))
select.select_by_visible_text("Services informatiques")

driver.find_element(By.CSS_SELECTOR, "input[type='submit'][value='Chercher']").click()
time.sleep(3)

# -------------------------------
# 5. Parcourir les pages de résultats
# -------------------------------
page = 1
while True:
    print(f"🔹 Scraping page {page}...")
    time.sleep(2)
    soup = BeautifulSoup(driver.page_source, "html.parser")

    rows = soup.select("table tr")[1:]  # ignorer l'entête
    if not rows:
        break

    for row in rows:
        cols = row.find_all("td")
        if len(cols) >= 4:
            nom = cols[0].get_text(strip=True)

            adresse = None
            secteur_activite = "Services informatiques"
            telephone = None
            email = None
            site_web = None
            lien_linkedin = None

            # 🔹 Récupérer le lien de la fiche via onclick
            onclick = row.get("onclick")
            lien_fiche = None
            if onclick:
                match = re.search(r"'(dbs\.asp\?action=result&ident=\d+)'", onclick)
                if match:
                    lien_fiche = match.group(1)

            # -------------------------------
            # 6. Aller sur la fiche entreprise
            # -------------------------------
            if lien_fiche:
                driver.get("https://www.tunisieindustrie.nat.tn/fr/" + lien_fiche)
                time.sleep(2)

                fiche_soup = BeautifulSoup(driver.page_source, "html.parser")
                fiche_rows = fiche_soup.select("table tr")
                infos = {}
                for fr in fiche_rows:
                    fr_cols = fr.find_all("td")
                    if len(fr_cols) == 2:
                        key = clean_key(fr_cols[0].get_text(strip=True))
                        val = fr_cols[1].get_text(strip=True)
                        infos[key] = val

                # Mapper vers notre BDD
                adresse = infos.get("adresse") 
                telephone = infos.get("telephone-siege/usine") or infos.get("téléphone-siège/usine")
                email = infos.get("e-mail") or infos.get("email")
                site_web = infos.get("url") or infos.get("site-web")
                # Linkedin pas fourni sur le site → reste None

                driver.back()
                time.sleep(2)

            # -------------------------------
            # 7. Insertion dans MySQL
            # -------------------------------
            cursor.execute("""
                INSERT INTO societes (nom, adresse, secteur_activite, telephone, email, site_web, lien_linkedin)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """, (nom, adresse, secteur_activite, telephone, email, site_web, lien_linkedin))
            db.commit()
            print(f"✅ Ajouté: {nom} | {email} | {site_web}")

    # -------------------------------
    # 8. Pagination
    # -------------------------------
    try:
        next_button = driver.find_element(By.XPATH, "//a[img[@src='images/dbibtnext.gif']]")
        next_button.click()
        time.sleep(2)
        page += 1
    except:
        print("🚩 Fin des pages.")
        break
    

driver.quit()
db.close()
print("🎉 Import terminé")
