import cv2
from pyzbar.pyzbar import decode

def scan_id_from_camera():
    cap = cv2.VideoCapture(0)  # 0 is usually the default webcam

    if not cap.isOpened():
        print("Could not open webcam")
        return

    print("Starting ID scan. Press 'q' to quit.")

    while True:
        ret, frame = cap.read()
        if not ret:
            print("Failed to grab frame")
            break

        # Detect and decode barcodes and QR codes
        decoded_objects = decode(frame)

        for obj in decoded_objects:
            # Draw a rectangle around the detected object
            points = obj.polygon
            if len(points) > 4: 
                hull = cv2.convexHull(np.array([point for point in points], dtype=np.float32))
                hull = list(map(tuple, np.squeeze(hull)))
            else:
                hull = points

            n = len(hull)
            for j in range(0, n):
                cv2.line(frame, hull[j], hull[ (j + 1) % n], (255,0,0), 3)

            # Decode the object
            data = obj.data.decode("utf-8")
            print("Detected ID Data:", data)

            # Show decoded data on frame
            cv2.putText(frame, data, (obj.rect.left, obj.rect.top - 10),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 2)

        # Display the frame
        cv2.imshow("ID Scanner", frame)

        # Quit with 'q'
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    scan_id_from_camera()
