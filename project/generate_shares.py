import random
import string
from captcha.image import ImageCaptcha
from PIL import Image, ImageDraw
import statistics
import os
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.image import MIMEImage
from email.mime.base import MIMEBase
from email import encoders
import sys
import mysql.connector
from PIL import Image
from io import BytesIO



#to client - img 2 - risk
#to server -


arguments = sys.argv
email = arguments[1]
voter_id = arguments[2]

# Email configuration
smtp_server = 'smtp.gmail.com'
smtp_port = 587
smtp_username = 'suryarajput20010@gmail.com'
smtp_password = 'lova ykys dnqx pkyn'
sender_email = 'suryarajput20010@gmail.com'
subject = 'Secret share for Voting System'

def generate_captcha_text(length=6):
    characters = string.ascii_uppercase + string.digits
    captcha_text = ''.join(random.choice(characters) for _ in range(length))
    return captcha_text

# Connect to the MySQL database
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='voting_system'
)

# Create a cursor object to interact with the database
cursor = conn.cursor()

def save_share_and_captcha(path_1,path_2, captcha,shift):
    try:
        # Open the image file
        with open(path_1, 'rb') as image_file_1:
            image_data_1 = image_file_1.read()
        with open(path_2, 'rb') as image_file_2:
            image_data_2 = image_file_2.read()

        # Insert the data into the database
        query = "INSERT INTO shares (voter_id,share_1,share_2, captcha, shift) VALUES (%s, %s, %s, %s, %s)"
        values = (voter_id,image_data_1,image_data_2,captcha,shift)
        cursor.execute(query, values)

        # Commit the transaction
        conn.commit()

    finally:
        # Close the cursor and connection
        cursor.close()
        if os.path.exists(path_1):
            # Delete the file
            os.remove(path_1)
        if os.path.exists(path_2):
            # Delete the file
            os.remove(path_2)
        conn.close()


def shift_and_replace(lst, shift_direction='left'):
    shift=random.randrange(50)
    size = len(lst)

    if shift_direction == 'left':
        # Shift left
        for i in range(shift):
            lst = lst[1:] + [0]
    elif shift_direction == 'right':
        # Shift right
        for i in range(shift):
            lst = [0] + lst[:-1]
    return shift,lst


def create_captcha_image(captcha_text):
    image_captcha = ImageCaptcha(fonts=[])
    captcha_image = image_captcha.generate_image(captcha_text)
    return captcha_image

def send_image(email, path,voter_id):
    receiver_email = email

    msg = MIMEMultipart()
    msg['From'] = sender_email
    msg['To'] = receiver_email
    msg['Subject'] = subject

    with open(path, 'rb') as image_file:
        image_data= image_file.read()
        image_attachment =  MIMEImage(image_data, name=os.path.basename(path))
        msg.attach(image_attachment)

    body = f'Please don\'t share this image with anybody and keep it ready when you want to log in to the page. \n Note :- The first 11 characters starting with `VCXXXXXXXXX` is your VOTER ID . That is {voter_id}'
    msg.attach(MIMEText(body, 'plain'))

    with smtplib.SMTP(smtp_server, smtp_port) as server:
        server.starttls()
        server.login(smtp_username, smtp_password)
        server.sendmail(sender_email, receiver_email, msg.as_string())

def generate_shares(voter_id):
    captcha_text = generate_captcha_text()
    captcha_image = create_captcha_image(captcha_text)
    captcha_image_path = f"{voter_id}_captcha.png"
    captcha_image.save(captcha_image_path)

    gs_image = captcha_image.convert('L')
    pixels = list(gs_image.getdata())
    mean_value = statistics.mean(pixels)
    stdev_value = statistics.stdev(pixels)

    threshold = mean_value - stdev_value
    bw_image = gs_image.point(lambda x: 0 if x < threshold else 1, '1')

    new_width = int((200 / bw_image.height) * bw_image.width)
    new_height = 200
    resized_image = bw_image.resize((new_width, new_height))

    cmp_image_path = f"{voter_id}_cmp_img.png"
    resized_image.save(cmp_image_path)

    secret_image = Image.open(cmp_image_path)
    share1 = Image.new('1', (secret_image.width * 2, secret_image.height * 2), 'white')
    share2 = Image.new('1', (secret_image.width * 2, secret_image.height * 2), 'white')

    for y in range(secret_image.height):
        for x in range(secret_image.width):
            pixel = secret_image.getpixel((x, y))
            if pixel == 0:
                share2.putpixel((2 * x, 2 * y), 1)
                share2.putpixel((2 * x, 2 * y + 1), 0)
                share2.putpixel((2 * x + 1, 2 * y), 0)
                share2.putpixel((2 * x + 1, 2 * y + 1), 1)
                share1.putpixel((2 * x, 2 * y), 1)
                share1.putpixel((2 * x, 2 * y + 1), 0)
                share1.putpixel((2 * x + 1, 2 * y), 0)
                share1.putpixel((2 * x + 1, 2 * y + 1), 1)
            else:
                share2.putpixel((2 * x, 2 * y), 0)
                share2.putpixel((2 * x, 2 * y + 1), 1)
                share2.putpixel((2 * x + 1, 2 * y), 1)
                share2.putpixel((2 * x + 1, 2 * y + 1), 0)
                share1.putpixel((2 * x, 2 * y), 1)
                share1.putpixel((2 * x, 2 * y + 1), 0)
                share1.putpixel((2 * x + 1, 2 * y), 0)
                share1.putpixel((2 * x + 1, 2 * y + 1), 1)

    share1_path = os.path.abspath(f"{voter_id}_share1.png")
    share2_path = os.path.abspath(f"{voter_id}_share2.png")
    
    data_2=list(share2.getdata())
    
    shift,data_2=shift_and_replace(data_2)

    share2.putdata(data_2)
    share2.save(share2_path)
    share1.save(share1_path)

    
    if os.path.exists(cmp_image_path):
            # Delete the file
            os.remove(cmp_image_path)
    if os.path.exists(captcha_image_path):
            # Delete the file
            os.remove(captcha_image_path)
    secret_image.close()
    
    
    return captcha_text,f"{voter_id}_share1.png",f"{voter_id}_share2.png",shift,

captcha,path_1,path_2,shift= generate_shares(voter_id)
send_image(email, path_2,voter_id)
save_share_and_captcha(path_1,path_2,captcha,shift)