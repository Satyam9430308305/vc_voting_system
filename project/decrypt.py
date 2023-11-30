from PIL import Image
import mysql.connector
from io import BytesIO
import os
import sys




arguments = sys.argv
voter_id = arguments[1]
img2 = arguments[2]

def shift_and_replace( lst, shift_direction='right',shift=()):
    n=list(shift)[0]
    if shift_direction == 'left':
        # Shift left
        for i in range(n):
            lst = lst[1:] + [0]
    elif shift_direction == 'right':
        # Shift right
        for i in range(n-1):
            lst = [0] + lst[:-1]
    return lst


def pad_images_to_equal_size(image1_path, image2_path):
    # Open the images
    image1 = Image.open(image1_path)
    image2 = Image.open(image2_path)

    # Get the dimensions of the images
    width1, height1 = image1.size
    width2, height2 = image2.size

    # Find the maximum width and height
    max_width = max(width1, width2)
    max_height = max(height1, height2)

    # Create a new image with the maximum dimensions
    padded_image1 = Image.new('1', (max_width, max_height), color='white')
    padded_image2 = Image.new('1', (max_width, max_height), color='white')

    # Paste the original images onto the padded images
    padded_image1.paste(image1, ((max_width - width1) // 2, (max_height - height1) // 2))
    padded_image2.paste(image2, ((max_width - width2) // 2, (max_height - height2) // 2))

    # Convert images to bytes
    padded_image1.save(image1_path)
    #padded_image1.show()
    image_data1 = list(padded_image1.getdata())
    
    padded_image2.save(image2_path)
    #padded_image2.show()
    image_data2 = list(padded_image2.getdata())

    # Close the images
    image1.close()
    image2.close()

    return image_data1, image_data2,max_width,max_height

def decrypt_and_compare(voter_id, img2):
    # Retrieve the stored image (blob) from the database
    shift=0
    cursor = db_connection.cursor()
    cursor.execute("SELECT share_1 FROM shares WHERE voter_id = %s", (voter_id,))
    stored_image_blob = cursor.fetchone()[0]
    cursor.execute("SELECT shift FROM shares WHERE voter_id = %s", (voter_id,))
    shift=cursor.fetchone()

    

    # Convert blob data to Image
    share1 = Image.open(BytesIO(stored_image_blob))

    # Get the path to the 'uploads' folder in the same directory as the script
    script_directory = os.path.dirname(os.path.abspath(__file__))
    uploads_folder = os.path.join(script_directory, "uploads")

    # Ensure the 'uploads' folder exists
    if not os.path.exists(uploads_folder):
        os.makedirs(uploads_folder)

    # Save the image as PNG in the 'uploads' folder
    img1_path = os.path.join(uploads_folder, f"{voter_id}_share1.png")
    img2_path = img2
    share1.save(img1_path, format="PNG")
    share1.close()

    # Get pixel data from shares
    p1, p2,new_width,new_height = pad_images_to_equal_size(img1_path, img2_path) 
    
    p2=shift_and_replace(p2,'right',shift)

    # XOR the pixel data
    overlay = [pixel1 ^ pixel2 for pixel1, pixel2 in zip(p1, p2)]

    # Create a new image with the merged data
    merged_image = Image.new('1', (new_width, new_height), 'white')
    merged_image.putdata(overlay)

    # Save the merged image in the 'uploads' folder
    merged_image_path = os.path.join(uploads_folder, f"{voter_id}_merged.png")
    merged_image.save(merged_image_path, format="PNG")
    merged_image.close()

    # Optionally return the paths for further usage
    print(merged_image_path)

# Example usage:
db_connection = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="voting_system"
)

decrypt_and_compare(voter_id,img2)

# Don't forget to close the database connection when done
db_connection.close()