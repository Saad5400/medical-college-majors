# Medical College Rotation Management System

A comprehensive Laravel application for managing medical student rotations across hospitals and healthcare centers. The system handles track (major) assignments and facility placements using GPA-based competitive algorithms.

## Table of Contents

- [System Overview](#system-overview)
- [User Roles](#user-roles)
- [How Tracks Work](#how-tracks-work)
- [How Specializations Work](#how-specializations-work)
- [Track Competition Algorithm](#track-competition-algorithm)
- [Facility Competition Algorithm](#facility-competition-algorithm)
- [Elective Months](#elective-months)
- [Custom Facilities](#custom-facilities)
- [Step-by-Step Example](#step-by-step-example)
- [Maintenance](#maintenance)

---

## System Overview

The system manages a multi-stage process for medical student rotations:

1. **Track Assignment**: Students submit ranked preferences for rotation tracks (majors/programs), and compete based on GPA for available spots
2. **Monthly Facility Assignment**: Students submit ranked preferences for hospitals/healthcare centers for each month, competing based on GPA for available seats

### Key Concepts

- **Track (Major)**: A rotation program lasting 12 months with scheduled specializations
- **Specialization**: A medical specialty (e.g., Surgery, Pediatrics) with a specific duration (1-2 months)
- **Facility**: A hospital or healthcare center offering seats for specific specializations each month
- **GPA-Based Competition**: Higher GPA students get first choice in both track and facility assignments
- **Wish System**: Students rank their preferences (up to 5 choices)

---

## User Roles

The system has four user roles:

| Role | Access Level | Capabilities |
|------|-------------|--------------|
| **Student** | Default | Submit track preferences, submit facility preferences, view assignments |
| **Leader** | Student + elevated privileges | Cannot submit track preferences (manually assigned), access to leader-only tracks |
| **Data Entry** | Staff | Import students, manage basic data |
| **Admin** | Full access | Manage all entities, run distribution algorithms, manually assign tracks to leaders |

### Role Assignment Rules

- All users without a role are assigned the **student** role by default
- Imported students automatically receive the **student** role
- Users can have multiple roles (e.g., both **student** and **leader**)
- Leaders are manually selected by admins via Filament user management
- Admins can manually assign users to tracks via relationship managers

---

## How Tracks Work

### What is a Track?

A **track** (also called a major or program) is a 12-month rotation plan that defines:
- Which **specializations** students will complete
- In which **months** each specialization occurs
- How many **students** can be enrolled (max capacity)
- Whether it's a **normal track** (visible to students) or **leader-only track** (hidden from regular students)

### Track Properties

- **Name**: e.g., "Internal Medicine Track", "Surgery Track"
- **Max Users**: Maximum number of students allowed (e.g., 30 students)
- **Is Leader Only**: If true, only leaders can be manually assigned to this track
- **Elective Months**: Specific months where students choose their own specialization (flexible scheduling)

### Track Scheduling

Tracks define a monthly schedule using **TrackSpecializations**:

**Example Track Schedule**:
```
Track: "Surgery Track" (Max Users: 25)

Month 1 (January):   General Surgery (1 month)
Month 2 (February):  Pediatric Surgery (1 month)
Month 3 (March):     Orthopedics (2 months) ──┐
Month 4 (April):     Orthopedics             ──┘
Month 5 (May):       [ELECTIVE MONTH]
Month 6 (June):      Cardiothoracic Surgery (1 month)
Month 7 (July):      Neurosurgery (1 month)
Month 8 (August):    [ELECTIVE MONTH]
Month 9 (September): Vascular Surgery (1 month)
Month 10 (October):  Trauma Surgery (2 months) ──┐
Month 11 (November): Trauma Surgery            ──┘
Month 12 (December): [ELECTIVE MONTH]
```

### Track Types

1. **Normal Tracks**: Visible to all students during registration
2. **Leader-Only Tracks**: Hidden from students, only assigned manually by admins to leaders

---

## How Specializations Work

### What is a Specialization?

A **specialization** is a medical specialty (e.g., Pediatrics, Surgery) with:
- **Name**: e.g., "Pediatric Surgery"
- **Duration**: 1 or 2 months (sometimes longer)
- **Facility Type**: Hospital or Healthcare Center
- **Color**: For visual identification in schedules

### Specialization-Track Relationship

Tracks can have the **same specialization multiple times** in different months:

**Example**:
```
Track: "Internal Medicine Track"

Month 1:  Cardiology (1 month)
Month 3:  Pulmonology (1 month)
Month 5:  Cardiology (1 month)  ← Same specialization, different month
Month 8:  Nephrology (2 months)
Month 10: Cardiology (1 month)  ← Same specialization again
```

**Rule**: Only **one specialization per month** per track (enforced by unique database constraint)

### Specialization-Facility Relationship

Facilities offer **seats** for specific specializations in specific months:

**Example**:
```
Hospital: "King Faisal Hospital"

January:
  - Pediatrics: 20 seats
  - Surgery: 15 seats
  - Cardiology: 10 seats

February:
  - Pediatrics: 18 seats
  - Surgery: 20 seats
  - Orthopedics: 12 seats
```

**Rule**: Facility type must match specialization type (Hospital specializations only at hospitals, Healthcare Center specializations only at healthcare centers)

---

## Track Competition Algorithm

### How Students Compete for Tracks

Students submit a **Registration Request** containing ranked track preferences:

**Process**:
1. **Registration Period Opens**: Students can create/edit registration requests
2. **Student Submits Preferences**: Students rank up to 5 tracks in order of preference
3. **Registration Period Closes**: Admin closes registration
4. **Admin Runs Distribution**: Algorithm assigns students to tracks

### Algorithm Logic

The system uses a **GPA-based preference matching algorithm**:

```
1. Reset all student track assignments to NULL
2. Load all students ordered by:
   - GPA (descending - highest first)
   - Registration request creation time (ascending - earliest first, as tie-breaker)
3. For each student (starting with highest GPA):
   a. Get their ranked track preferences (1st choice, 2nd choice, etc.)
   b. For each preference in order:
      - Check if track has available capacity
      - If YES: Assign student to track, reduce capacity, STOP
      - If NO: Try next preference
   c. If no tracks have capacity, student remains unassigned
```

### Example Scenario

**Tracks Available**:
```
Track A (Surgery):         Max 2 students
Track B (Internal Medicine): Max 2 students
Track C (Pediatrics):      Max 1 student
```

**Student Registrations**:
```
Student 1 (GPA: 3.9):
  1st choice: Track A
  2nd choice: Track B
  3rd choice: Track C

Student 2 (GPA: 3.8):
  1st choice: Track A
  2nd choice: Track C

Student 3 (GPA: 3.7):
  1st choice: Track A
  2nd choice: Track B

Student 4 (GPA: 3.6):
  1st choice: Track B
  2nd choice: Track C

Student 5 (GPA: 3.5):
  1st choice: Track C
  2nd choice: Track B
```

**Algorithm Execution**:
```
Process Student 1 (GPA 3.9):
  - Wants Track A (capacity: 2/2 available) → ✓ Assigned to Track A
  - Track A capacity: 1/2 remaining

Process Student 2 (GPA 3.8):
  - Wants Track A (capacity: 1/2 available) → ✓ Assigned to Track A
  - Track A capacity: 0/2 remaining (FULL)

Process Student 3 (GPA 3.7):
  - Wants Track A (capacity: 0/2 available) → ✗ Full
  - Wants Track B (capacity: 2/2 available) → ✓ Assigned to Track B
  - Track B capacity: 1/2 remaining

Process Student 4 (GPA 3.6):
  - Wants Track B (capacity: 1/2 available) → ✓ Assigned to Track B
  - Track B capacity: 0/2 remaining (FULL)

Process Student 5 (GPA 3.5):
  - Wants Track C (capacity: 1/1 available) → ✓ Assigned to Track C
  - Track C capacity: 0/1 remaining (FULL)
```

**Final Assignments**:
```
Track A: Student 1 (3.9), Student 2 (3.8)
Track B: Student 3 (3.7), Student 4 (3.6)
Track C: Student 5 (3.5)
```

---

## Facility Competition Algorithm

### How Students Compete for Hospital/Healthcare Center Placements

After track assignment, students submit **Facility Registration Requests** for each month:

**Process**:
1. **Student Has Track Assigned**: Required before facility registration
2. **Registration Period Opens**: Students can create facility requests
3. **Student Submits Monthly Requests**: For each month, student ranks up to 5 facility wishes
4. **Admin Runs Distribution**: Algorithm assigns students to facilities **one month at a time**

### Monthly Requests

Students create **one request per month** with up to **5 ranked wishes**:

**Example**:
```
Student: Ahmed (Track: Surgery Track, GPA: 3.8)

Month 1 (January - General Surgery):
  Wish 1: King Faisal Hospital (competitive)
  Wish 2: National Guard Hospital (competitive)
  Wish 3: Prince Sultan Hospital (competitive)
  Wish 4: Custom Hospital XYZ (non-competitive)
  Wish 5: King Fahad Hospital (competitive, but ignored due to wish 4)

Month 2 (February - Pediatric Surgery):
  Wish 1: Children's Hospital (competitive)
  Wish 2: King Khalid Hospital (competitive)
  Wish 3: Custom Clinic ABC (non-competitive)
```

### Algorithm Logic (Per Month)

The system processes **one month at a time**:

```
1. Admin selects a month to distribute (e.g., January)
2. Reset all facility assignments for that month to NULL
3. Load all facility requests for that month ordered by:
   - User GPA (descending - highest first)
4. For each request (starting with highest GPA):
   a. Get competitive wishes only (is_competitive = true)
   b. For each competitive wish in order:
      - Check if facility has available seats for this specialization in this month
      - If YES: Assign student to facility, reduce capacity, STOP
      - If NO: Try next competitive wish
   c. If no wishes have capacity, student remains unassigned for this month
```

### Example Scenario

**Facility Seats Available (January - General Surgery)**:
```
King Faisal Hospital:   2 seats
National Guard Hospital: 1 seat
Prince Sultan Hospital: 1 seat
```

**Student Facility Requests (January)**:
```
Student A (GPA: 3.9):
  Wish 1: King Faisal Hospital (competitive)
  Wish 2: National Guard Hospital (competitive)

Student B (GPA: 3.8):
  Wish 1: King Faisal Hospital (competitive)
  Wish 2: Prince Sultan Hospital (competitive)

Student C (GPA: 3.7):
  Wish 1: King Faisal Hospital (competitive)
  Wish 2: National Guard Hospital (competitive)

Student D (GPA: 3.6):
  Wish 1: National Guard Hospital (competitive)
  Wish 2: Prince Sultan Hospital (competitive)

Student E (GPA: 3.5):
  Wish 1: King Faisal Hospital (competitive)
  Wish 2: Custom Hospital (non-competitive)
  Wish 3: National Guard Hospital (competitive, but ignored)
```

**Algorithm Execution**:
```
Process Student A (GPA 3.9):
  - Wants King Faisal (capacity: 2/2 available) → ✓ Assigned
  - King Faisal capacity: 1/2 remaining

Process Student B (GPA 3.8):
  - Wants King Faisal (capacity: 1/2 available) → ✓ Assigned
  - King Faisal capacity: 0/2 remaining (FULL)

Process Student C (GPA 3.7):
  - Wants King Faisal (capacity: 0/2 available) → ✗ Full
  - Wants National Guard (capacity: 1/1 available) → ✓ Assigned
  - National Guard capacity: 0/1 remaining (FULL)

Process Student D (GPA 3.6):
  - Wants National Guard (capacity: 0/1 available) → ✗ Full
  - Wants Prince Sultan (capacity: 1/1 available) → ✓ Assigned
  - Prince Sultan capacity: 0/1 remaining (FULL)

Process Student E (GPA 3.5):
  - Wants King Faisal (capacity: 0/2 available) → ✗ Full
  - Wants Custom Hospital (non-competitive) → Out of competition
  - Wish 3 (National Guard) is ignored because Wish 2 was custom
  - Student E is NOT assigned by system (goes to custom hospital)
```

**Final Assignments (January)**:
```
King Faisal Hospital:   Student A (3.9), Student B (3.8)
National Guard Hospital: Student C (3.7)
Prince Sultan Hospital: Student D (3.6)
Unassigned:            Student E (3.5) - chose custom hospital
```

---

## Elective Months

### What are Elective Months?

**Elective months** are flexible months in a track's schedule where students choose their own specialization and facility, rather than following the pre-defined track schedule.

### How Elective Months Work

**Track Schedule Example**:
```
Track: "Surgery Track"

Month 1: General Surgery (required)
Month 2: Pediatric Surgery (required)
Month 3: [ELECTIVE MONTH] ← Student chooses
Month 4: Orthopedics (required)
```

**During Elective Months**:
- Students can select **any specialization** (not limited to track schedule)
- Students can select **any facility** offering that specialization
- Students can choose **custom specializations** (non-competitive)

**Non-Elective Months**:
- Specialization is **determined by track schedule**
- Students only choose facility
- Cannot select custom specialization

### Elective Month Examples

**Example 1: Student chooses pre-defined specialization**
```
Student: Sara (Track: Surgery Track, Month 3 is elective)

Facility Request for Month 3:
  Specialization: Cardiology (chosen by student)
  Wish 1: Heart Center Hospital (competitive)
  Wish 2: Cardiac Care Center (competitive)

→ Student competes for these facilities based on GPA
```

**Example 2: Student chooses custom specialization**
```
Student: Omar (Track: Surgery Track, Month 3 is elective)

Facility Request for Month 3:
  Specialization: Custom - "International Research Program"
  Wish 1: Custom Hospital "Mayo Clinic USA" (non-competitive)

→ Student exits competition, wishes after this are ignored
```

### Elective Month Rules

1. **Track Configuration**: Admins define elective months in track settings (stored as JSON array)
2. **No Conflicts**: Elective months cannot overlap with scheduled specializations
3. **Validation**: System prevents creating specialization schedules in elective months
4. **Flexibility**: Encourages students to explore different specializations

---

## Custom Facilities

### What are Custom Facilities?

**Custom facilities** are hospitals or healthcare centers **not registered in the system**. Students can enter these manually by name.

### How Custom Facilities Work

When a student selects a custom facility:
1. Student toggles **"Is Custom"** in the facility wish form
2. Student enters **custom facility name** (text field)
3. For elective months, student can also enter **custom specialization name**
4. The wish is marked as **non-competitive** (`is_competitive = false`)

### Critical Rule: Competition Exit

**Once a student selects a custom facility, they exit the competition for that month entirely, and ALL subsequent wishes are ignored.**

### Example Scenario

**Student Registration**:
```
Student: Fatima (GPA: 3.9)

Facility Request for January:
  Wish 1: King Faisal Hospital (competitive)
  Wish 2: National Guard Hospital (competitive)
  Wish 3: Custom - "Private Clinic ABC" (non-competitive) ← EXIT COMPETITION
  Wish 4: Prince Sultan Hospital (ignored, not competitive)
  Wish 5: King Khalid Hospital (ignored, not competitive)
```

**What Happens**:
```
During distribution algorithm:
1. Check Wish 1 (King Faisal) - If available, assign here
2. Check Wish 2 (National Guard) - If available, assign here
3. Reach Wish 3 (Custom) - STOP processing
4. Wish 4 and Wish 5 are NEVER evaluated

Result:
- If Wish 1 or Wish 2 had capacity, student gets assigned
- If both were full, student is NOT assigned by system
- Student goes to their custom facility (outside system tracking)
```

### Custom Facility Use Cases

1. **International Rotations**: "Johns Hopkins Hospital - USA"
2. **Private Clinics**: "Dr. Abdullah's Private Practice"
3. **Research Institutions**: "King Abdullah Research Center"
4. **Rural Health Centers**: "Al-Qassim Rural Clinic"

### Why Custom Facilities Exit Competition

**Rationale**: If a student is willing to go to a custom facility (wish 3), they're essentially saying "I prefer this custom facility over all remaining registered facilities." Therefore, the system doesn't need to compete for wish 4-5 because the student has already made their decision to go outside the system.

---

## Step-by-Step Example

Let's follow a complete student journey through the system:

### Student Profile
```
Name: Ahmed Mohammed
GPA: 3.75
Student ID: 2024001
Role: Student
```

### Step 1: Track Registration

**Available Tracks**:
```
1. Surgery Track (Max: 30 students, Normal)
2. Internal Medicine Track (Max: 25 students, Normal)
3. Pediatrics Track (Max: 20 students, Normal)
4. Research Track (Max: 5 students, Leader-Only) ← Ahmed cannot see this
```

**Ahmed's Registration Request**:
```
1st Choice: Surgery Track
2nd Choice: Internal Medicine Track
3rd Choice: Pediatrics Track
```

**Competition Outcome**:
- 45 students registered for tracks
- Ahmed ranked 12th by GPA
- Surgery Track fills with top 30 students
- Ahmed (GPA 3.75) is assigned to **Surgery Track** (his 1st choice)

### Step 2: Understanding Track Schedule

Ahmed's assigned track schedule:
```
Month 1 (Jan):  General Surgery (Hospital, 1 month)
Month 2 (Feb):  Pediatric Surgery (Hospital, 1 month)
Month 3 (Mar):  Orthopedics (Hospital, 2 months) ──┐
Month 4 (Apr):  Orthopedics (continues)         ──┘
Month 5 (May):  [ELECTIVE MONTH]
Month 6 (Jun):  Cardiothoracic Surgery (Hospital, 1 month)
Month 7 (Jul):  Neurosurgery (Hospital, 1 month)
Month 8 (Aug):  [ELECTIVE MONTH]
Month 9 (Sep):  Vascular Surgery (Hospital, 1 month)
Month 10 (Oct): Trauma Surgery (Hospital, 2 months) ──┐
Month 11 (Nov): Trauma Surgery (continues)        ──┘
Month 12 (Dec): [ELECTIVE MONTH]
```

### Step 3: Facility Registration (Month 1 - January)

**Ahmed's Request for January (General Surgery)**:
```
Specialization: General Surgery (determined by track)
Month: 1 (January)

Wishes:
  1. King Faisal Hospital (20 seats available, competitive)
  2. National Guard Hospital (15 seats available, competitive)
  3. Prince Sultan Hospital (10 seats available, competitive)
  4. King Khalid Hospital (8 seats available, competitive)
  5. King Fahad Hospital (12 seats available, competitive)
```

**Competition Outcome**:
- 30 students from Surgery Track compete for January
- Ahmed ranked 12th by GPA
- King Faisal fills with top 20 students
- Ahmed (GPA 3.75) is assigned to **National Guard Hospital** (his 2nd choice)

### Step 4: Facility Registration (Month 3 - March)

**Ahmed's Request for March (Orthopedics, 2-month rotation)**:
```
Specialization: Orthopedics (determined by track)
Month: 3 (March)
Duration: 2 months (March + April)

Wishes:
  1. Orthopedic Hospital (15 seats available, competitive)
  2. King Saud Hospital (10 seats available, competitive)
  3. National Guard Hospital (8 seats available, competitive)
```

**Competition Outcome**:
- Ahmed (GPA 3.75) is assigned to **Orthopedic Hospital** (his 1st choice)
- This assignment covers both March AND April

### Step 5: Facility Registration (Month 5 - May, ELECTIVE)

**Ahmed's Request for May (Elective Month)**:
```
Specialization: Cardiology (Ahmed's choice - not in track schedule)
Month: 5 (May)

Wishes:
  1. Heart Center Hospital (12 seats available, competitive)
  2. King Fahad Cardiac Center (8 seats available, competitive)
  3. Custom - "Mayo Clinic USA Rotation" (non-competitive)
```

**Competition Outcome**:
- Ahmed (GPA 3.75) competes for wishes 1 and 2 only
- Heart Center fills with higher GPA students
- King Fahad Cardiac Center still has seats
- Ahmed is assigned to **King Fahad Cardiac Center** (his 2nd choice)
- Wish 3 (custom) is never reached

### Step 6: Final Schedule Summary

**Ahmed's Complete Year**:
```
January:         National Guard Hospital - General Surgery
February:        [Need to register]
March - April:   Orthopedic Hospital - Orthopedics (2 months)
May:             King Fahad Cardiac Center - Cardiology (elective)
June onwards:    [Need to register for remaining months]
```

---

## Maintenance

### Downloading a local copy of the database:

1. Connect to the vps via ssh:
   ```bash
   ssh root@78.47.152.41
   ```

2. Locate the database container id:
   ```bash
   docker ps # 1d7db2a958cb
   ```

3. Create a dump of the database:
   ```bash
   docker exec -it 1d7db2a958cb pg_dump -U postgres -d postgres > postgres_backup.sql
    ```

4. Exit the vps:
   ```bash
   exit
   ```

5. Copy the dump and the storage to your local machine:
   ```bash
   scp root@78.47.152.41:./postgres_backup.sql .
    ```

6. Restore the dump to your local database:
   ```bash
   psql -U admin -h localhost -d medical_college -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
   psql -U admin -h localhost -d medical_college -f postgres_backup.sql
   ```
